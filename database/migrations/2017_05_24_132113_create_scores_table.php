<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('score_name');
            $table->integer('score_value_since_day1')->default(1)->comment('this value should never be reseted');
            $table->integer('score_value')->default(1)->comment('this value can be reseted, after a report for instance');
            $table->integer('website_id')->unsigned();
            $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
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
        Schema::dropIfExists('scores');
    }
}
