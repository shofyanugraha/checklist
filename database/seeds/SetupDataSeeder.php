<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ObjectData;

class SetupDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$user = new User;
    	$user->name = 'User 1';
    	$user->email = 'user@example.com' ;
    	$user->password = Hash::make('password');
    	$user->save();

    	$object = new ObjectData;
    	$object->user_id = $user->id;
    	$object->name = 'Object 1';
    	$object->object_domain = 'deals';
    	$object->save();
    }
}
