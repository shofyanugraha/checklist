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
    	$user->name = 'User';
    	$user->email = 'user@example.com' ;
    	$user->password = Hash::make('password');
    	$user->save();

    	for($i = 0; $i < 50; $i++){
    		$user = new User;
	    	$user->name = 'User '.$i;
	    	$user->email = 'user'.$i.'@example.com' ;
	    	$user->password = Hash::make('password');
	    	$user->save();
    	}

    	$object = new ObjectData;
    	$object->user_id = $user->id;
    	$object->name = 'Object 1';
    	$object->object_domain = 'deals';
    	$object->save();

    	$object = new ObjectData;
    	$object->user_id = $user->id;
    	$object->name = 'Object 2';
    	$object->object_domain = 'deals';
    	$object->save();

    	$object = new ObjectData;
    	$object->user_id = $user->id;
    	$object->name = 'Object 3';
    	$object->object_domain = 'deals';
    	$object->save();
    }
}
