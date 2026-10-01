<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_https_proxy_request_generates_https_asset_urls(): void
    {
        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '172.16.5.1'])
            ->withHeaders([
                'Host' => 'otaniverse.org',
                'X-Forwarded-For' => '203.0.113.10',
                'X-Forwarded-Host' => 'otaniverse.org',
                'X-Forwarded-Port' => '443',
                'X-Forwarded-Proto' => 'https',
            ])
            ->get('/admin/login');

        $response->assertOk();
        $response->assertSee('https://otaniverse.org/build/assets/', false);
        $response->assertDontSee('http://otaniverse.org/build/assets/', false);
    }
}
