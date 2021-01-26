<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('subscription_id');
            $table->string('customer_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->integer('log_id')->nullable();
            $table->integer('order_count')->default(0);
            $table->dateTime('event_time')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('subscription_id');
            $table->string('customer_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->dateTime('next_ship_date')->nullable();
            $table->dateTime('last_ship_date')->nullable();
            $table->dateTime('purchase_date')->nullable();
            $table->string('coupon')->nullable();
            $table->integer('coupon_id');
            $table->integer('interval_type')->nullable(); // 1-Day; 2-Week;3-Month;5-Yearh
            $table->integer('interval_number')->nullable();
            $table->float('subtotal');
            $table->float('total');
            $table->float('tax');
            $table->float('shipping');
            $table->dateTime('transaction_date')->nullable();
            $table->integer('log_id')->nullable();
            $table->dateTime('event_time')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['subscription_id', 'customer_id', 'first_name', 'last_name', 'email', 'log_id', 'order_count', 'event_time']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subscription_id', 'customer_id', 'first_name', 'last_name', 'email', 'purchase_date',
                                'next_ship_date', 'last_ship_date', 'coupon', 'coupon_id', 'interval_type', 'interval_number',
                                'log_id', 'event_time',
                                'subtotal', 'total', 'tax', 'shipping', 'transaction_date']);
        });
    }
}
