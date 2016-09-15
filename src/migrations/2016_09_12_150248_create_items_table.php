<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_items', function ( Blueprint $table )
        {
            $table->increments('id');
            $table->integer('transaction_id', false, true);
            $table->string('description')->nullable();
            $table->decimal('amount')->nullable();
            $table->dateTime('charged_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transaction_items');
    }
}
