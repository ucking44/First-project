<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loyalty_program_id')->nullable();
            $table->foreign('loyalty_program_id')->references('id')->on('loyalty_programs');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->bigInteger('cron_id')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('loyalty_number')->index();
            $table->string('first_name')->index();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->index();
            $table->string('phone_number')->nullable()->unique();
            $table->string('email')->index()->unique()->nullable();
            $table->string('token')->nullable();
            $table->tinyInteger('receive_notification')->default(1);
            $table->string('gender')->nullable();
            $table->decimal('current_bal', 15,2)->default(0.00);
            $table->decimal('total_credit', 15,2)->default(0.00);
            $table->decimal('total_debit', 15,2)->default(0.00);
            $table->decimal('blocked_points', 15,2)->default(0.00);
            $table->string('member_reference'); //like BVN
            $table->tinyInteger('first_login')->default(0);
            $table->dateTime('first_login_time')->nullable();
            $table->tinyInteger('terms_agreed')->default(0);
            $table->dateTime('last_change_password')->nullable();
            $table->string('password');
            $table->string('pin')->nullable();
            $table->integer('enrollment_status')->default(0)->nullable();
            $table->integer('tries')->default(0)->nullable()->comment('counts the number of times the enrollment migration has been tried');
            $table->date('birthday')->nullable();
            $table->date('anniversary')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->date('date_enrolled')->nullable();
            $table->foreign('branch_code')->references('id')->on('branches');
            $table->foreign('tier_id')->references('id')->on('tiers');
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
        Schema::dropIfExists('enrollments');
    }
}
