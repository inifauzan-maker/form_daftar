<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'admin@villamerah.com',
                'password' => bcrypt('password123'),
                'jabatan' => 'Kadiv Marketing',
                'is_active' => true,
                'role' => 'kadiv_marketing'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'socmed@villamerah.com',
                'password' => bcrypt('password123'),
                'jabatan' => 'Social Media Specialist',
                'is_active' => true,
                'role' => 'social_media_specialist'
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'creator@villamerah.com',
                'password' => bcrypt('password123'),
                'jabatan' => 'Content Creator',
                'is_active' => true,
                'role' => 'content_creator'
            ],
            [
                'name' => 'Maria Gonzales',
                'email' => 'ads@villamerah.com',
                'password' => bcrypt('password123'),
                'jabatan' => 'Ads Specialist',
                'is_active' => true,
                'role' => 'ads_specialist'
            ],
            [
                'name' => 'Rini Puspita',
                'email' => 'analyst@villamerah.com',
                'password' => bcrypt('password123'),
                'jabatan' => 'Data Analyst',
                'is_active' => true,
                'role' => 'data_analyst'
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = \App\Models\User::create($userData);
            
            // Assign role to user
            $roleModel = \App\Models\Role::where('name', $role)->first();
            if ($roleModel) {
                $user->roles()->attach($roleModel->id);
            }
        }
    }
}
