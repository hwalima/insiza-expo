<?php

namespace Database\Seeders;

use App\Models\Expo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        foreach (['super_admin', 'admin', 'exhibitor', 'guest'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Super-admin account
        $admin = User::firstOrCreate(
            ['email' => 'admin@insizaexpo.co.zw'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('Admin@1234!'),
                'phone'    => '+263777000001',
            ]
        );
        $admin->assignRole('super_admin');

        // Active Expo 2026
        $expo2026 = Expo::firstOrCreate(
            ['year' => 2026],
            [
                'name'       => 'Insiza District Industrial Expo 2026',
                'start_date' => '2026-09-16',
                'end_date'   => '2026-09-18',
                'venue'      => 'Filabusi Show Grounds',
                'theme'      => 'Industrialisation for Economic Development',
                'is_active'  => true,
                'contact_email' => 'info@insizaexpo.co.zw',
                'contact_phone' => '+263000000000',
            ]
        );

        // Archived Expo 2024
        $expo2024 = Expo::firstOrCreate(
            ['year' => 2024],
            [
                'name'       => 'Insiza District Industrial Expo 2024',
                'start_date' => '2024-09-17',
                'end_date'   => '2024-09-19',
                'venue'      => 'Filabusi Show Grounds',
                'theme'      => 'Connect, Collaborate and Grow',
                'is_active'  => false,
            ]
        );

        // 2024 Guest of Honour
        \App\Models\GuestOfHonor::firstOrCreate(
            ['expo_id' => $expo2024->id],
            [
                'name'         => 'Dr Evelyn Ndlovu',
                'title'        => 'Minister of State for Matabeleland South Provincial Affairs and Devolution',
                'organisation' => 'Government of Zimbabwe',
                'bio'          => 'Dr Evelyn Ndlovu is the Minister of State for Matabeleland South Provincial Affairs and Devolution. She has been instrumental in driving industrial and economic development in the Insiza District, championing the vision of connecting local producers with national and regional markets.',
                'photo'        => 'https://africarenewal.un.org/sites/default/files/styles/interviewee_new_home_page_image/public/interviewee/dr-evelyne-ndlovu.jpg',
            ]
        );
    }
}

