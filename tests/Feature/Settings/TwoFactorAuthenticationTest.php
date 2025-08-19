<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_two_factor_settings_component()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings/two-factor')
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/TwoFactor')
            );
    }

    public function test_passes_correct_props_to_two_factor_component()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings/two-factor')
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/TwoFactor')
                ->has('requiresConfirmation')
                ->where('requiresConfirmation', true)
                ->has('twoFactorEnabled')
                ->where('twoFactorEnabled', false)
            );
    }

    public function test_shows_two_factor_disabled_status_initially()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings/two-factor')
            ->assertInertia(fn (Assert $page) => $page
                ->where('twoFactorEnabled', false)
            );
    }

    public function test_shows_two_factor_status_reflects_user_state()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication');

        $this->get('/settings/two-factor')
            ->assertInertia(fn (Assert $page) => $page
                ->where('twoFactorEnabled', $user->fresh()->hasEnabledTwoFactorAuthentication())
            );
    }

    public function test_requires_confirmation_prop_matches_fortify_config()
    {
        $user = User::factory()->create();
        $expectedRequiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');

        $this->actingAs($user)
            ->get('/settings/two-factor')
            ->assertInertia(fn (Assert $page) => $page
                ->where('requiresConfirmation', $expectedRequiresConfirmation)
            );
    }

    public function test_two_factor_settings_page_requires_authentication()
    {
        $this->get('/settings/two-factor')
            ->assertRedirect('/login');
    }
}
