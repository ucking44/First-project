<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $table = "transactions";
    public function up()
    {
        //

        Schema::create('transactions', function (Blueprint $table) {
            //$table->dropForeign('transactions_member_id_foreign');
            $table->id();
            $table->string('member_cif')->nullable();
            $table->string('account_number')->nullable();
            $table->string('product_code')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->double('amount', 20, 2)->nullable();
            $table->string('branch_code')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('channel')->nullable();
            $table->string('transaction_type')->nullable();
            $table->bigInteger('transaction_log_id')->nullable();
            $table->date('transaction_date')->nullable();
            $table->date('dumped_date')->nullable();
            $table->bigInteger('cron_id')->nullable();
            //$table->unsignedBigInteger('cron_id')->nullable();
            $table->integer('status')->default(0);
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
}
