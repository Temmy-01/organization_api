<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_type_names = [
            ['user_type_name' => "System Administrator", 'created_at' => now()],
            ['user_type_name' => "Administration", 'created_at' => now()],
            ['user_type_name' => "Base User", 'created_at' => now()],
            ['user_type_name' => "Sub Account", 'created_at' => now()],
        ];

        DB::table('user_types')->insert($user_type_names);
    }
}
