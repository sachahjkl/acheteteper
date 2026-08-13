#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
tmp=$(mktemp -d)
port=${TEST_PORT:-18080}
server_pid=

cleanup() {
  if [ -n "$server_pid" ]; then
    kill "$server_pid" 2>/dev/null || true
    wait "$server_pid" 2>/dev/null || true
  fi
  rm -rf "$tmp"
}
trap cleanup EXIT INT TERM

export DB_PATH="$tmp/database.db"
export UPLOADS_PATH="$tmp/uploads"
export DEBUG=false
export TEST_ROOT="$root"
export APP_CONFIG="$tmp/config.php"
mkdir -p "$UPLOADS_PATH"

cat >"$APP_CONFIG" <<'PHP'
<?php

use Acheteteper\ConfigBuilder;

$root = getenv('TEST_ROOT');
return (new ConfigBuilder())
    ->setViewDir($root . '/src/views')
    ->setDbPath(getenv('DB_PATH'))
    ->setUserConfig('uploadsPath', getenv('UPLOADS_PATH'))
    ->setAllowedMethods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'CONNECT', 'QUERY'])
    ->enableCsrfProtection()
    ->build();
PHP

php -S "127.0.0.1:$port" -t "$root/src/public" "$root/src/public/index.php" >"$tmp/server.log" 2>&1 &
server_pid=$!

i=0
until curl -fsS "http://127.0.0.1:$port/" >/dev/null; do
  i=$((i + 1))
  if [ "$i" -ge 30 ]; then
    cat "$tmp/server.log" >&2
    exit 1
  fi
  sleep 0.2
done

request() {
  expected=$1
  shift
  status=$(curl -sS -o "$tmp/body" -D "$tmp/headers" -w '%{http_code}' "$@")
  if [ "$status" != "$expected" ]; then
    printf 'Expected HTTP %s, got %s for %s\n' "$expected" "$status" "$*" >&2
    cat "$tmp/body" >&2
    exit 1
  fi
}

request 200 "http://127.0.0.1:$port/"
grep -q 'Achetétéper' "$tmp/body"

request 200 "http://127.0.0.1:$port/api/users"
grep -q 'application/json' "$tmp/headers"
grep -q 'John Doe' "$tmp/body"

request 200 -H 'HX-Request: true' "http://127.0.0.1:$port/realtime/search?q=web"
grep -q 'WebSocket' "$tmp/body"
if grep -qi '<!DOCTYPE html>' "$tmp/body"; then
  printf 'Live search returned the full page layout\n' >&2
  exit 1
fi
if grep -q 'Server-Sent Events' "$tmp/body"; then
  printf 'Live search returned an unrelated result\n' >&2
  exit 1
fi

request 200 --max-time 5 "http://127.0.0.1:$port/realtime/events"
grep -qi '^Content-Type: text/event-stream' "$tmp/headers"
grep -q 'event: tick' "$tmp/body"
grep -q 'id: 5' "$tmp/body"

request 404 "http://127.0.0.1:$port/routing/show/extra"
request 404 "http://127.0.0.1:$port/uploads"
request 404 --path-as-is "http://127.0.0.1:$port/uploads/../config/app.php"

request 405 -X TRACE "http://127.0.0.1:$port/request"
grep -qi '^Allow: .*QUERY' "$tmp/headers"

request 204 -X OPTIONS "http://127.0.0.1:$port/request"
grep -qi '^Allow: GET, HEAD, POST, OPTIONS, QUERY' "$tmp/headers"

request 403 -X POST --data 'action=add&name=blocked' "http://127.0.0.1:$port/db"
grep -q 'Invalid CSRF token' "$tmp/body"

curl -sS -c "$tmp/cookies" "http://127.0.0.1:$port/db" >"$tmp/form"
token=$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' "$tmp/form" | head -n 1)
if [ -z "$token" ]; then
  printf 'CSRF token not found\n' >&2
  exit 1
fi
request 302 -b "$tmp/cookies" --data-urlencode "_token=$token" --data 'action=add&name=saved' "http://127.0.0.1:$port/db"
grep -qi '^Location: /db' "$tmp/headers"
request 200 -b "$tmp/cookies" "http://127.0.0.1:$port/db"
grep -q 'saved' "$tmp/body"

printf '\211PNG\r\n\032\ninvalid image data' >"$tmp/fake.png"
request 200 -b "$tmp/cookies" -F "_token=$token" -F "file=@$tmp/fake.png;type=image/png" "http://127.0.0.1:$port/upload/submit"
grep -q 'File must be an image' "$tmp/body"

printf 'HTTP tests passed\n'
