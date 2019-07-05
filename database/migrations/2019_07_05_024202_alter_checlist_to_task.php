<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCheclistToTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('checklists', 'tasks');

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('type');
            $table->unsignedInteger('updated_by')->nullable()->change();
            $table->datetime('completed_at')->nullable()->change();
        });
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('updated_by')->nullable()->change();
            $table->datetime('completed_at')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::rename('tasks', 'checklists');
    }
}
