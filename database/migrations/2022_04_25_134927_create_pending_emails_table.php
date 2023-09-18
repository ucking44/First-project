<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_emails', function (Blueprint $table) {
            $table->id();
            //////$table->integer('enrolment_id');    I commented this
            ////////$table->string('enrolment_id')->change(); I commented this
            $table->integer('template_id');
            $table->string('enrolment_id');    //////  I added this
            $table->integer('status')->default(0); //->after('template_id')->default(0);
            $table->integer('tries')->default(0); //->after('status')->default(0);
            $table->string('subject')->default('Fidelity Green Reward Notification');
            $table->text('body');//->default();
            $table->string('from')->default('greenrewards@loyaltysolutionsnigeria.com');
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
        Schema::dropIfExists('pending_emails');
    }
}
