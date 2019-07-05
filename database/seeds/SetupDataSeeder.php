<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SetupDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'User 1',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);
		
		DB::table('objects')->insert([
            'domain_name' => 'User 1',
            'user_id' => 'User 1',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
