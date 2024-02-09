<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payment_method_id')->nullable();
            $table->uuid('student_id')->nullable();
            $table->string('payment_code')->nullable();
            $table->dateTime('expiry_time')->nullable();
            $table->bigInteger('pay_amount');
            $table->dateTime('paid_at')->nullable();
            $table->string('status');
            $table->string('xendit_id')->nullable();
            $table->string('payment_link')->nullable();
            $table->integer('xendit_fee')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
