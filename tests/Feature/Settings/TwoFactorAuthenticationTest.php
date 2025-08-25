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

    public function test_two_factor_settings_page_requires_authentication()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $this->get(route('two-factor.show'))
            ->assertRedirect(route('login'));
    }

    public function test_renders_two_factor_settings_component()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show'))
            ->assertInertia(fn(Assert $page) => $page
                ->component('settings/TwoFactor')
                ->where('twoFactorEnabled', false)
            );
    }

    public function test_two_factor_settings_page_requires_password_confirmation()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }
        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('two-factor.show'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_two_factor_settings_page_does_not_requires_password_confirmation_when_disabled()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $user = User::factory()->create();

         config(['fortify.features' => [
            Features::twoFactorAuthentication([
                'confirm' => false,
                'confirmPassword' => false,
            ]),
        ]]);

        // Debug what Features::optionEnabled returns
        dump(Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'));

        $this->actingAs($user)
            ->get(route('two-factor.show'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('settings/TwoFactor')
            );
    }

    public function test_two_factor_authentication_disabled_returns_forbidden_when_disabled()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show'))
            ->assertForbidden();
    }

    public function test_validates_two_factor_authentication_state_when_confirmation_disabled()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        config(['fortify.features' => [
            Features::twoFactorAuthentication([
                'confirm' => true,
                'confirmPassword' => true,
            ]),
        ]]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show'))
            ->assertOk();
    }

    public function test_validates_two_factor_authentication_state_when_confirmation_required_and_transitioning()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        config(['fortify.features' => [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->withSession(['two_factor_empty_at' => time() - 10]);

        $this->post(route('two-factor.enable'));

        $this->get(route('two-factor.show'))
            ->assertOk();

        $this->assertNotNull(session('two_factor_confirming_at'));
    }

    public function test_validates_two_factor_authentication_state_when_user_never_finished_confirming()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        config(['fortify.features' => [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]]);

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ])->save();

        $pastTime = time() - 10;

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->withSession(['two_factor_confirming_at' => $pastTime]);

        $response = $this->get(route('two-factor.show'));

        $response->assertOk();

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNotNull(session('two_factor_empty_at'));
        $this->assertNull(session('two_factor_confirming_at'));
    }

    public function test_validates_two_factor_authentication_state_when_confirmation_not_required()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        config(['fortify.features' => [
            Features::twoFactorAuthentication(['confirm' => false]),
        ]]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()]);

        $this->get(route('two-factor.show'))
            ->assertOk();

        $this->assertNull(session('two_factor_empty_at'));
        $this->assertNull(session('two_factor_confirming_at'));
    }
}
