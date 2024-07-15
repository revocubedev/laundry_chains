<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromColumnInItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            $table->foreignId('from')->constrained('departments')->onUpdate('cascade')->onDelete('cascade')->after('extra_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            //
        });
    }
}
