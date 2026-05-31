<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocaleSwitchAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_switch_returns_json_when_requested(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get(route('locale.switch', ['locale' => 'en']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'locale' => 'en',
            ]);
    }

    public function test_locale_switch_returns_json_with_accept_json_only(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get(route('locale.switch', ['locale' => 'ar']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'locale' => 'ar',
            ]);
    }

    public function test_locale_switch_redirects_for_normal_browser_navigation(): void
    {
        $response = $this->from('/')->get(route('locale.switch', ['locale' => 'ar']));

        $response->assertRedirect('/');
    }
}
