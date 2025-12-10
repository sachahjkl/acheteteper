<?php

namespace Acheteteper;

/**
 * Simple CSRF token generation and validation.
 * 
 * @package Acheteteper
 */
class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate and store a CSRF token.
     * 
     * @return string CSRF token.
     */
    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Generate a hidden input field HTML for CSRF token.
     * 
     * @return string HTML input field.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    /**
     * Validate a CSRF token.
     * 
     * @param string|null $token Token to validate (defaults to POST['_token']).
     * @return bool True if token is valid.
     */
    public static function validate(?string $token = null): bool
    {
        if ($token === null) {
            $token = Request::fromGlobals()->post('_token');
        }
        $sessionToken = Session::get(self::TOKEN_KEY);
        return $sessionToken !== null && hash_equals($sessionToken, $token ?? '');
    }

    /**
     * Check if CSRF token is valid, throw exception if not.
     * 
     * @param string|null $token Token to validate.
     * @return void
     * @throws HttpException If token is invalid.
     */
    public static function verify(?string $token = null): void
    {
        if (!self::validate($token)) {
            throw new HttpException(403, 'Invalid CSRF token');
        }
    }
}
