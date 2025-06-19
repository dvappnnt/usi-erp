<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\ApprovalLevelSetting;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Create a Company
        $company = Company::create([
            'name' => 'Panelco I',
            'email' => 'panelco_one@yahoo.com',
            'address' => 'Bani, Pangasinan',
            'description' => "PANGASINAN I ELECTRIC COOPERATIVE (PANELCO I) is the exclusive franchise holder to operate an electric light and power services in the City of Alaminos and Municipalities of Agno, Anda, Bani, Bolinao, Burgos, Dasol, Infanta, and Mabini - all in the province of Pangasinan.",
            'mobile' => '9285027969',
            'website' => 'https://panelcoi.com/',
            'created_by_user_id' => null,  // Temporarily null, will update after user creation
            'country_id' => 177,
            'avatar' => 'app-settings/app-logo.png',
            'created_at' => now(),
            'updated_at' => now(),
            'token' => \Illuminate\Support\Str::random(64),
        ]);

        // Create Super Admin User after company is created
        $superAdmin = User::factory()->withPersonalTeam()->create([
            'name' => 'Super Admin User',
            'email' => 'super.admin@panelcoi.com',
            'password' => bcrypt('123123123'),
            'company_id' => $company->id,
        ]);

        $admin = User::factory()->withPersonalTeam()->create([
            'name' => 'Admin User',
            'email' => 'admin@panelcoi.com',
            'password' => bcrypt('123123123'),
            'company_id' => $company->id,
        ]);

        // Assign the super-admin role to the created user
        $superAdmin->assignRole('super-admin');
        $admin->assignRole('admin');

        // Update the company with the created_by_user_id
        $company->update(['created_by_user_id' => $superAdmin->id]);

        // Create Approval Level Settings
        ApprovalLevelSetting::create([
            'type' => 'purchase-order',
            'company_id' => 1,
            'user_id' => $superAdmin->id,
            'level' => 2,
            'label' => 'Checked By:'
        ]);

        ApprovalLevelSetting::create([
            'type' => 'purchase-order',
            'company_id' => 1,
            'user_id' => $admin->id,
            'level' => 1,
            'label' => 'Approved By:'
        ]);
    }
}
