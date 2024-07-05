<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->decimal('bill', 9, 2)->default(0.00);
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->dateTime('dateTimeIn');
            $table->dateTime('dateTimeOut');
            $table->string('note');
            $table->string('status');
            $table->decimal('discount', 9, 2)->default(0.00);
            $table->boolean('isExpress')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->decimal('paidAmount', 9, 2)->default(0.00);
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->enum('paymentType', ['card', 'bank', 'cash', 'wallet', 'cheque', 'transfer']);
            $table->foreignId('delivery_id')->constrained('delivery_options')->onDelete('cascade');
            $table->decimal('vat', 9, 2)->default(0);
            $table->decimal('revenue', 9, 2)->default(0);
            $table->float('discount_percentage')->nullable();
            $table->decimal('extra_discount_value', 9, 2)->default(0);
            $table->float('extra_discount_percentage')->nullable();
            $table->text('summary')->nullable();
            $table->string('serial_number');
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
        Schema::dropIfExists('orders');
    }
}
