<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function ( Blueprint $table )
        {
            $table->increments('id');
            $table->morphs('plannable');
            $table->string('card_number')->nullable();
            $table->string('card_cvv')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_issue_number')->nullable();
            $table->string('currency')->nullable()->default('GBP');
            $table->dateTime('card_starts_at')->nullable();
            $table->dateTime('card_expires_at')->nullable();
            $table->dateTime('last_failed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index([ 'plannable_type', 'plannable_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('plans');
    }
}
