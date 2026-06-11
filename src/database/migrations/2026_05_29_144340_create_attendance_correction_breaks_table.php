<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectionBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correction_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained('attendance_correction_requests')->cascadeOnDelete();
            $table->time('requested_break_start')->nullable();
            $table->time('requested_break_end')->nullable();
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
        Schema::dropIfExists('attendance_correction_breaks');
    }
}
