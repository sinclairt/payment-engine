<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function ( Blueprint $table )
        {
            $table->increments('id');
            $table->integer('plan_id', false, true);
            $table->string('reference')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_cvv')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_issue_number')->nullable();
            $table->string('currency')->nullable()->default('GBP');
            $table->dateTime('card_starts_at')->nullable();
            $table->dateTime('card_expires_at')->nullable();
            $table->boolean('is_success');
            $table->boolean('is_failure');
            $table->json('gateway_response')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
