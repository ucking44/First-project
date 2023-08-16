<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionReportLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_report_logs', function (Blueprint $table) {
            $table->id();
            $table->string('customer_reference')->nullable();
            $table->string('account_number')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('status_code')->nullable();
            $table->text('status_message')->nullable();
            $table->string('transaction_date')->nullable();
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
        Schema::dropIfExists('transaction_report_logs');
    }
}
