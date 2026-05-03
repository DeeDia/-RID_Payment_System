<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected const RATELIMIT_ATTEMPTS = 7;

    /**
     * Creates the application.
     * Standard Laravel application factory — required by RefreshDatabase.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Creates and persists a customer User for use in authenticated tests.
     */
    protected function createCustomer(array $overrides = []): \App\Models\User
    {
        return \App\Models\User::factory()->create(array_merge([
            'role' => 'customer',
        ], $overrides));
    }

    /**
     * Creates and persists an employee User.
     */
    protected function createEmployee(array $overrides = []): \App\Models\User
    {
        return \App\Models\User::factory()->create(array_merge([
            'role' => 'employee',
        ], $overrides));
    }

    /**
     * Returns a valid payment payload that passes all whitelist rules.
     */
    protected function validPaymentPayload(array $overrides = []): array
    {
        return array_merge([
            'amount'              => '1500.00',
            'currency'            => 'USD',
            'provider'            => 'SWIFT',
            'swift_code'          => 'NEDSZAJJ',
            'beneficiary_account' => 'GB29NWBK60161331',
            'beneficiary_name'    => 'John Smith',
            'reference'           => 'INV-001',
        ], $overrides);
    }

    /**
     * Returns a valid registration payload.
     */
    protected function validRegistrationPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name'            => 'Jane Doe',
            'id_number'            => '9001015009087',
            'account_number'       => '1234567890',
            'password'             => 'SecureP@ss1',
            'password_confirmation'=> 'SecureP@ss1',
        ], $overrides);
    }

}
