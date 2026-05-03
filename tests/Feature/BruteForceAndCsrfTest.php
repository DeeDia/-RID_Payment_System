<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class BruteForceAndCsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_blocked_after_5_failed_attempts(): void
    {
        $customer = $this->createCustomer([
            'account_number' => '9999999999',
            'password' => Hash::make('SecureP@ss1'),
        ]);

        RateLimiter::clear('login:' . request()->ip());

        // 5 failed attempts
        for ($i = 0; $i < self::RATELIMIT_ATTEMPTS; $i++) {
            $response = $this->postJson('/login', [
                'account_number' => '9999999999',
                'password' => 'WrongPassword!1',
            ]);
            
            $response->assertStatus(302);
        }

        // 6th attempt must be rate-limited
        $response = $this->postJson('/login', [
            'account_number' => '9999999999',
            'password' => 'SecureP@ss1', // even correct password is blocked
        ]);

        $throttleError = session('errors.default.messages.throttle.0');
        $this->assertStringContainsString('Too many login attempts', $throttleError);
    }

    public function test_rate_limit_key_includes_both_account_number_and_ip(): void
    {

        // Exhaust rate limit for user 1
        for ($i = 0; $i < self::RATELIMIT_ATTEMPTS; $i++) {
            $this->postJson('/login', [
                'account_number' => '1111111111',
                'password' => 'WrongP@ss1!',
            ], [
                'REMOTE_ADDR' => '192.168.1.100',
            ]);
        }

        // User 2 from different addr must NOT be blocked
        $customer2 = $this->createCustomer([
            'account_number' => '2222222222',
            'password' => Hash::make('SecureP@ss1'),
        ]);

        $response = $this->postJson('/login', [
            'account_number' => '2222222222',
            'password' => 'SecureP@ss1',
        ], [
            'REMOTE_ADDR' => '192.168.1.102',
        ]);

        $response->assertRedirect();
    }

    public function test_wrong_password_returns_generic_error_message(): void
    {
        $customer = $this->createCustomer([
            'account_number' => '1234567890',
            'password' => Hash::make('SecureP@ss1'),
        ]);

        $response = $this->postJson('/login', [
            'account_number' => '1234567890',
            'password' => 'WrongP@ss1!',
        ]);

        $response->assertStatus(302);
        $error = session('errors.default.messages.credentials.0');

        $this->assertSame(
            'Invalid account number or password.',
            $error,
            'Error message must be generic — must not reveal whether the account exists.'
        );
    }

    public function test_non_existent_account_returns_same_generic_error_message(): void
    {
        // Account 9876543210 does not exist
        RateLimiter::clear('login:' . request()->ip());

        $response = $this->postJson('/login', [
            'account_number' => '9876543210',
            'password' => 'AnyP@ssword1',
        ]);

        $response->assertStatus(302);
        $error = session('errors.default.messages.credentials.0');

        $this->assertEquals(
            'Invalid account number or password.',
            $error,
            'Non-existent account must return identical message to wrong password — no enumeration.'
        );
    }

    public function test_wrong_password_and_non_existent_account_produce_identical_error(): void
    {
        $customer = $this->createCustomer([
            'account_number' => '1111111111',
            'password' => Hash::make('SecureP@ss1'),
        ]);

        $wrongPasswordResponse = $this->postJson('/login', [
            'account_number' => '1111111111',
            'password' => 'WrongP@ss1!',
        ], [
            'REMOTE_ADDR' => '192.168.1.120',
        ]);

        $errorOne = session('errors.default.messages.credentials.0');


        $nonExistentResponse = $this->postJson('/login', [
            'account_number' => '9999999999',
            'password' => 'AnyP@ss1!',
        ], [
            'REMOTE_ADDR' => '192.168.1.121',
        ]);
            
        $errorTwo = session('errors.default.messages.credentials.0');

        $this->assertEquals(
            $errorOne,
            $errorTwo,
            'Wrong-password and non-existent-account errors must be identical to prevent user enumeration.'
        );
    }

    public function test_error_message_does_not_reveal_account_existence(): void
    {
        $response = $this->postJson('/login', [
            'account_number' => '9999999999',
            'password' => 'AnyP@ss1!',
        ]);

        $error = session('errors.default.messages.credentials.0') ?? '';

        $this->assertStringNotContainsString('not found', strtolower($error));
        $this->assertStringNotContainsString('does not exist', strtolower($error));
        $this->assertStringNotContainsString('no account', strtolower($error));
    }
}
