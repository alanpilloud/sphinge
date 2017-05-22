<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterceptorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interceptor_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hash', 32);
            $table->boolean('muted')->default(0);
            $table->string('type');
            $table->text('message');
            $table->string('file');
            $table->integer('line');
            $table->string('url')->comment('The url on which the error happened');
            $table->integer('occurences')->default(1);
            $table->timestamp('last_occurence');
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
        Schema::dropIfExists('interceptor_logs');
    }
}
