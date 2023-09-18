<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailReportLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_report_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enrollment_id'); //->after('id');
            $table->string('subject'); //->after('enrollment_id');
             $table->integer('status'); //->after('enrollment_id');
             $table->string('email'); //->after('status');
             $table->text('email_body'); //->after('email');
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
        Schema::dropIfExists('email_report_logs');
    }
}
