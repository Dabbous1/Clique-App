<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => '$2a$12$dmcpny0BuA.E6Bs212t3vekk1ip7H19wQ7SLLP1Gxz1QrRt82KU0.',
            'role_id' => '1',
        ]);
    }
}
