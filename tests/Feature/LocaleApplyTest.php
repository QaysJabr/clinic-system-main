<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocaleApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_apply_returns_full_html_json(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->postJson(route('locale.apply'), [
            'locale' => 'ar',
            'path' => '/',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['ok', 'html'])
            ->assertJson(['ok' => true]);

        $html = $response->json('html');
        $this->assertIsString($html);
        $this->assertStringContainsString('locale-swap-root', $html);
    }

    public function test_locale_apply_returns_redirect_hint_when_route_redirects(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->postJson(route('locale.apply'), [
            'locale' => 'en',
            'path' => '/dashboard',
        ]);

        $this->assertContains($response->status(), [409, 422]);
    }
}
