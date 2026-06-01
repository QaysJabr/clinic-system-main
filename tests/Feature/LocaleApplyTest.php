<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocaleApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_apply_sets_session_and_signals_reload(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->postJson(route('locale.apply'), [
            'locale' => 'ar',
            'path' => '/',
        ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'locale' => 'ar',
                'reload' => true,
            ]);
    }

    public function test_locale_apply_rejects_invalid_locale(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->postJson(route('locale.apply'), [
            'locale' => 'fr',
            'path' => '/',
        ])->assertUnprocessable();
    }
}
