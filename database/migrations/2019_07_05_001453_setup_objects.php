<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupObjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('object_domain');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('checklists', function($table){
            $table->unsignedInteger('object_id')->nullable()->change();
            $table->foreign('object_id')->references('id')->on('objects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checklists', function($table){
            $table->dropForeign('checklists_object_id_foreign');
            
        });
        Schema::table('checklists', function($table){
            $table->string('object_id')->change();
        });

        Schema::table('objects', function($table){
            $table->dropForeign(['user_id']);
        });

        Schema::drop('objects');
    }
}
