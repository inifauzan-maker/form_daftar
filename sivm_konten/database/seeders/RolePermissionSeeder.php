<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard'],
            ['name' => 'manage_content', 'display_name' => 'Manage Content'],
            ['name' => 'create_content', 'display_name' => 'Create Content'],
            ['name' => 'edit_content', 'display_name' => 'Edit Content'],
            ['name' => 'delete_content', 'display_name' => 'Delete Content'],
            ['name' => 'publish_content', 'display_name' => 'Publish Content'],
            ['name' => 'approve_content', 'display_name' => 'Approve Content'],
            ['name' => 'view_analytics', 'display_name' => 'View Analytics'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users'],
            ['name' => 'manage_campaigns', 'display_name' => 'Manage Campaigns'],
            ['name' => 'view_reports', 'display_name' => 'View Reports'],
            ['name' => 'manage_ads', 'display_name' => 'Manage Advertisements'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::create($permission);
        }

        // Create roles
        $roles = [
            [
                'name' => 'kadiv_marketing',
                'display_name' => 'Kadiv Marketing',
                'description' => 'Head of Marketing Division - Full access to all features',
                'permissions' => ['view_dashboard', 'manage_content', 'view_analytics', 'manage_users', 'manage_campaigns', 'view_reports', 'approve_content']
            ],
            [
                'name' => 'social_media_specialist',
                'display_name' => 'Social Media Specialist',
                'description' => 'Manages social media content and engagement',
                'permissions' => ['view_dashboard', 'manage_content', 'create_content', 'edit_content', 'publish_content', 'view_analytics']
            ],
            [
                'name' => 'ads_specialist',
                'display_name' => 'Ads Specialist',
                'description' => 'Manages paid advertising campaigns',
                'permissions' => ['view_dashboard', 'manage_ads', 'view_analytics', 'manage_campaigns', 'view_reports']
            ],
            [
                'name' => 'content_creator',
                'display_name' => 'Content Creator',
                'description' => 'Creates content for marketing campaigns',
                'permissions' => ['view_dashboard', 'create_content', 'edit_content', 'view_analytics']
            ],
            [
                'name' => 'sales_team',
                'display_name' => 'Sales Team',
                'description' => 'Sales team with limited content access',
                'permissions' => ['view_dashboard', 'view_analytics', 'view_reports']
            ],
            [
                'name' => 'data_analyst',
                'display_name' => 'Data Analyst',
                'description' => 'Analyzes content performance and metrics',
                'permissions' => ['view_dashboard', 'view_analytics', 'view_reports']
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = \App\Models\Role::create($roleData);
            
            // Attach permissions to role
            $permissionIds = \App\Models\Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->attach($permissionIds);
        }
    }
}
