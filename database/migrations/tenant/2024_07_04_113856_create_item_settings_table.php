<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->text('patterns')->nullable();
            $table->text('stains')->nullable();
            $table->text('materials')->nullable();
            $table->text('styles')->nullable();
            $table->text('color')->nullable();
            $table->longText('brand')->nullable();
            $table->longText('notes')->nullable();
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
        Schema::dropIfExists('item_settings');
    }
}
