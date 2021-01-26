<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('logs', function (Blueprint $table) {
//            $table->id();
//            $table->smallInteger('type')->comment('1:Subscription; 2:Order; 3: Customer');
//            $table->text('raw');
//            $table->text('upcomingProducts')->nullable();
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('logs');
    }
}
