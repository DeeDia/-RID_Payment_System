<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $employees = [
            [
                'full_name'   => 'Sarah Johnson',
                'employee_id' => 'EMP00001',
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
