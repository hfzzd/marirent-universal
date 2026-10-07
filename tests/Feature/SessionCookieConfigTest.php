<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SessionCookieConfigTest extends TestCase
{
    use DatabaseTransactions;

    public function test_session_cookie_config_is_string(): void
    {
        $cookieConfig = config('session.cookie');

        $this->assertIsString(
            $cookieConfig,
            'session.cookie must be a string (cookie name), not array. '
            .'Current type: '.gettype($cookieConfig)
        );
    }

    public function test_session_cookie_name_is_not_empty(): void
    {
        $cookieName = config('session.cookie');

        $this->assertNotEmpty(
            $cookieName,
            'session.cookie name cannot be empty'
        );
    }

    public function test_session_path_is_string(): void
    {
        $this->assertIsString(
            config('session.path'),
            'session.path must be a string'
        );
    }

    public function test_session_domain_nullable(): void
    {
        $domain = config('session.domain');

        $this->assertTrue(
            $domain === null || is_string($domain),
            'session.domain must be null or string'
        );
    }

    public function test_session_secure_is_bool(): void
    {
        $this->assertIsBool(
            config('session.secure'),
            'session.secure must be boolean'
        );
    }

    public function test_session_http_only_is_bool(): void
    {
        $this->assertIsBool(
            config('session.http_only'),
            'session.http_only must be boolean'
        );
    }

    public function test_session_same_site_is_valid(): void
    {
        $sameSite = config('session.same_site');

        $this->assertTrue(
            in_array($sameSite, ['lax', 'strict', 'none'], true) || $sameSite === null,
            'session.same_site must be one of: lax, strict, none, null'
        );
    }
}
