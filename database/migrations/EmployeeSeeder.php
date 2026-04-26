<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * EmployeeSeeder
 *
 * Employees are NEVER self-registered via the portal.
 * They are seeded into the database during onboarding by an admin.
 * Passwords are set externally and communicated securely to each employee.
 */
class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'full_name'   => 'Sarah Johnson',
                'employee_id' => 'EMP00001',
                // In real onboarding, generate a strong random password and deliver
                // it securely to the employee via encrypted email or password manager
                'password'    => Hash::make('Emp!Secure#2024A', ['rounds' => 12]),
                'role'        => 'employee',
            ],
            [
                'full_name'   => 'Michael van der Berg',
                'employee_id' => 'EMP00002',
                'password'    => Hash::make('Emp!Secure#2024B', ['rounds' => 12]),
                'role'        => 'employee',
            ],
        ];

        foreach ($employees as $employee) {
            User::updateOrCreate(
                ['employee_id' => $employee['employee_id']],
                $employee
            );
        }
    }
}
