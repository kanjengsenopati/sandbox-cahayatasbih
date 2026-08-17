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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id');
            $table->uuid('school_id')->nullable();
            $table->uuid('academic_year_id');
            $table->uuid('bill_type_id');
            $table->integer('total_students')->default(0);
            $table->bigInteger('total_amount')->default(0);
            $table->string('status')->default('ACTIVE'); // ACTIVE or ROLLED_BACK
            $table->timestamp('rolled_back_at')->nullable();
            $table->uuid('rolled_back_by')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
            $table->foreign('bill_type_id')->references('id')->on('bill_types');
            $table->foreign('rolled_back_by')->references('id')->on('admins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_logs');
    }
};
