<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id', 15)->primary();
            $table->string('appointment_uuid')->nullable();
            $table->integer('patient_id')->nullable();
            $table->jsonb('prescription');
            $table->string('payment_type');
            $table->decimal('total_amount', 20, 2);
            $table->decimal('payment_amount', 20, 2);
            $table->decimal('change', 20, 2)->nullable();
            $table->string('discount_type')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->jsonb('additional_info')->nullable();
            $table->blameColumns();
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
        Schema::dropIfExists('transactions');
    }
};
