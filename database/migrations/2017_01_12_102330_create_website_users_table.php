<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsiteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('website_users', function (Blueprint $table) {
             $table->engine = 'InnoDB';
             $table->uuid('id')->primary();
             $table->integer('remote_id');
             $table->string('login');
             $table->string('registered');
             $table->string('email');
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
         Schema::table('website_users', function(Blueprint $table) {
             $table->dropForeign(['website_id']);
         });
         Schema::dropIfExists('website_users');
     }
}
