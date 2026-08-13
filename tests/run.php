<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/Fuzz.php';

use Acheteteper\Config;
use Acheteteper\ConfigBuilder;
use Acheteteper\Csrf;
use Acheteteper\HttpException;
use Acheteteper\Request;
use Acheteteper\Response;
use Acheteteper\Session;
use Acheteteper\SqliteDataSource;
use Acheteteper\ServerSentEvents;
use Tests\Fuzz;

$tests = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[$name] = $callback;
}

function assertSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertTrue(bool $value, string $message = 'Expected true'): void
{
    if (!$value) {
        throw new RuntimeException($message);
    }
}

function assertThrows(string $class, callable $callback, ?int $status = null): void
{
    try {
        $callback();
    } catch (Throwable $error) {
        assertTrue($error instanceof $class, 'Expected ' . $class . ', got ' . $error::class);
        if ($status !== null) {
            assertSame($status, $error->getStatus());
        }
        return;
    }
    throw new RuntimeException('Expected ' . $class . ' to be thrown');
}

test('config builder applies HTTP policies', function (): void {
    $config = (new ConfigBuilder())
        ->setViewDir(dirname(__DIR__) . '/src/views')
        ->setAllowedMethods(['get', 'QUERY', 'get'])
        ->setPublicUrl('https://example.test/')
        ->setTrustedProxies(['127.0.0.1'])
        ->setMaxJsonBodyBytes(8)
        ->enableCsrfProtection()
        ->build();

    assertSame(['GET', 'QUERY'], $config->allowedMethods());
    assertSame('https://example.test', $config->publicUrl());
    assertSame(['127.0.0.1'], $config->trustedProxies());
    assertSame(8, $config->maxJsonBodyBytes());
    assertTrue($config->csrfProtection());
});

test('config builder rejects invalid values', function (): void {
    assertThrows(InvalidArgumentException::class, fn() => (new ConfigBuilder())->setAllowedMethods([]));
    assertThrows(InvalidArgumentException::class, fn() => (new ConfigBuilder())->setMaxJsonBodyBytes(0));
    assertThrows(InvalidArgumentException::class, fn() => (new ConfigBuilder())->setViewDir('/missing/views'));
});

test('config resolves debug mode once per request', function (): void {
    $calls = 0;
    $config = new Config(debugResolver: function () use (&$calls): bool {
        $calls++;
        return false;
    });
    assertTrue(!$config->debug());
    assertTrue(!$config->debug());
    assertSame(1, $calls);
});

test('request handles standard and QUERY methods', function (): void {
    $request = new Request([], [], ['REQUEST_METHOD' => 'QUERY', 'REQUEST_URI' => '/items?q=1'], '{"active":true}');
    assertTrue($request->isQuery());
    assertTrue($request->isMethod('query'));
    assertTrue($request->isSafe());
    assertSame('/items', $request->path());
    assertSame(['active' => true], $request->json());

    $post = new Request([], [], ['REQUEST_METHOD' => 'POST']);
    assertTrue(!$post->isSafe());
});

test('request validates JSON and its configured limit', function (): void {
    assertThrows(HttpException::class, fn() => (new Request([], [], [], '{'))->json(), 400);

    $config = new Config(maxJsonBodyBytes: 2);
    assertThrows(HttpException::class, fn() => (new Request([], [], [], '{} ', $config))->json(), 413);
});

test('request trusts forwarded addresses only from configured proxies', function (): void {
    $server = [
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_X_FORWARDED_FOR' => '203.0.113.9, 127.0.0.1',
        'REQUEST_URI' => '/path?x=1',
    ];
    assertSame('127.0.0.1', (new Request([], [], $server))->ip());

    $config = new Config(publicUrl: 'https://public.test', trustedProxies: ['127.0.0.1']);
    $request = new Request([], [], $server, null, $config);
    assertSame('203.0.113.9', $request->ip());
    assertSame('https://public.test/path?x=1', $request->url());
});

test('response stores normalized headers and JSON', function (): void {
    $response = (new Response())
        ->setHeader('X-Test', 'first')
        ->setHeader('x-test', 'second')
        ->json(['ok' => true], 201);

    assertSame(201, $response->getStatus());
    assertSame('second', $response->getHeader('X-Test'));
    assertSame(['x-test' => 'second', 'Content-Type' => 'application/json; charset=utf-8'], $response->getHeaders());
    assertSame('{"ok":true}', $response->getBody());
});

test('response configures an SSE stream', function (): void {
    $response = (new Response())->eventStream(function (): void {});
    assertTrue($response->isStreamed());
    assertSame('text/event-stream', $response->getHeader('Content-Type'));
    assertSame('no', $response->getHeader('X-Accel-Buffering'));
});

test('SSE encodes event fields and multiline data', function (): void {
    ob_start();
    ServerSentEvents::send("first\nsecond", "tick\nignored", "1\nignored");
    $event = ob_get_clean();
    assertSame("id: 1ignored\nevent: tickignored\ndata: first\ndata: second\n\n", $event);
});

test('CSRF accepts one session token and rejects another', function (): void {
    $token = Csrf::token();
    assertTrue(Csrf::validate($token));
    assertTrue(!Csrf::validate(str_repeat('0', 64)));
});

test('request reads CSRF headers case-insensitively', function (): void {
    $request = new Request([], [], ['HTTP_X_CSRF_TOKEN' => 'header-token']);
    assertSame('header-token', $request->header('x-csrf-token'));
});

test('session flash values are consumed once', function (): void {
    Session::setFlash('notice', 'saved');
    assertSame('saved', Session::flash('notice'));
    assertSame(null, Session::flash('notice'));
});

test('SQLite enforces foreign keys and rolls back failures', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'acheteteper-db-');
    try {
        $db = new SqliteDataSource(new Config(dbPath: $path));
        $db->execute('CREATE TABLE parent (id INTEGER PRIMARY KEY)');
        $db->execute('CREATE TABLE child (parent_id INTEGER REFERENCES parent(id))');
        assertThrows(PDOException::class, fn() => $db->execute('INSERT INTO child(parent_id) VALUES (99)'));

        assertThrows(RuntimeException::class, function () use ($db): void {
            $db->transaction(function (SqliteDataSource $db): void {
                $db->execute('INSERT INTO parent(id) VALUES (1)');
                throw new RuntimeException('rollback');
            });
        });
        assertSame(0, (int) $db->scalar('SELECT COUNT(*) FROM parent'));
    } finally {
        @unlink($path);
    }
});

test('fuzz request methods are normalized', function (): void {
    (new Fuzz(0xAC4E))->repeat(500, function (Fuzz $fuzz): void {
        $method = $fuzz->string(1, 24, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $request = new Request([], [], ['REQUEST_METHOD' => $method]);
        assertSame(strtoupper($method), $request->method());
        assertTrue($request->isMethod(strtolower($method)));
    });
});

test('fuzz valid JSON round trips', function (): void {
    (new Fuzz(0xB0D1))->repeat(500, function (Fuzz $fuzz): void {
        $value = $fuzz->jsonValue();
        $body = json_encode($value, JSON_THROW_ON_ERROR);
        assertSame($value, (new Request([], [], [], $body))->json());
    });
});

test('fuzz malformed JSON is rejected', function (): void {
    (new Fuzz(0xBAD))->repeat(500, function (Fuzz $fuzz): void {
        $body = json_encode($fuzz->jsonValue(), JSON_THROW_ON_ERROR) . '#';
        assertThrows(HttpException::class, fn() => (new Request([], [], [], $body))->json(), 400);
    });
});

test('fuzz request paths exclude query strings', function (): void {
    (new Fuzz(0xA11CE))->repeat(500, function (Fuzz $fuzz): void {
        $segment = $fuzz->string(0, 40, 'abcdefghijklmnopqrstuvwxyz0123456789-._~');
        $query = $fuzz->string(0, 40, 'abcdefghijklmnopqrstuvwxyz0123456789=&');
        $request = new Request([], [], ['REQUEST_URI' => '/' . $segment . '?' . $query]);
        assertSame('/' . $segment, $request->path());
    });
});

$failures = 0;
foreach ($tests as $name => $callback) {
    try {
        $callback();
        fwrite(STDOUT, "ok - $name\n");
    } catch (Throwable $error) {
        $failures++;
        fwrite(STDERR, "not ok - $name\n  " . $error->getMessage() . "\n");
    }
}

if (session_status() === PHP_SESSION_ACTIVE) {
    Session::destroy();
}

fwrite(STDOUT, count($tests) . " tests, $failures failures\n");
exit($failures === 0 ? 0 : 1);
