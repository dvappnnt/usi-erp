<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $locations = [
            [
                'name' => 'Panelco I',
                'address' => 'Bani, Pangasinan',
            ],
        ];

        foreach ($locations as $location) {
            $companyId = rand(1, 2); // Randomly assign to company 1 or 2
            DB::table('warehouses')->insert([
                'company_id' => $companyId,
                'token' => Str::random(64),
                'slug' => Str::slug($location['name']),
                'created_by_user_id' => $companyId,
                'category_id' => null,
                'country_id' => 177, // Philippines
                'name' => $location['name'],
                'email' => null,
                'landline' => null,
                'mobile' => null,
                'address' => $location['address'],
                'description' => "Facility located at {$location['address']}",
                'website' => null,
                'avatar' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
