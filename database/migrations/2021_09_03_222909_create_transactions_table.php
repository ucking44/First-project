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
            $table->string('member_cif');
            $table->string('account_number')->nullable();
            $table->string('product_code');
            $table->bigInteger('quantity')->nullable();
            $table->decimal('amount', 20, 2);
            $table->string('branch_code');
            $table->string('transaction_reference');
            $table->string('channel');
            $table->string('transaction_type');
            $table->bigInteger('transaction_log_id');
            $table->date('transaction_date');
            $table->date('dumped_date');
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
