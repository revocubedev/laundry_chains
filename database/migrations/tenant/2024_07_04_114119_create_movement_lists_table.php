<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movement_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('location_id')->constrained()->nullable()->onDelete('cascade');
            $table->foreignId('store_rep_id')->constrained('users')->nullable()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->nullable()->onDelete('cascade');
            $table->text('order_ids')->nullable();
            $table->string('total_bags')->nullable();
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
        Schema::dropIfExists('movement_lists');
    }
}
