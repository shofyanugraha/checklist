<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('name');
            $table->json('checklist');
            $table->json('items');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('checklists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('object_domain');
            $table->string('object_id');
            $table->string('description');
            $table->integer('is_completed');
            $table->datetime('completed_at');
            $table->unsignedInteger('updated_by');
            $table->datetime('due');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('description');
            $table->integer('is_completed');
            $table->datetime('completed_at');
            $table->datetime('due');
            $table->integer('urgency');
            $table->unsignedInteger('updated_by');
            $table->unsignedInteger('assignee_id')->index();
            $table->unsignedInteger('task_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('assignee_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('checklists');
        });

        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->json('attributes');
            $table->integer('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function($table){
            $table->dropForeign(['task_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('checklists', function($table){
            $table->dropForeign(['user_id']);
            $table->dropForeign(['updated_by']);
        });

        Schema::table('templates', function($table){
            $table->dropForeign(['user_id']);
        });

        Schema::drop('items');
        Schema::drop('checklists');
        Schema::drop('templates');
    }
}
