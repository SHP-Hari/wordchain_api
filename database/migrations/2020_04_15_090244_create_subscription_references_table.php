<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_references', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('subscriber_phone_no', 20);
            $table->string('subscriber_user_hash', 255)->nullable();
            $table->string('device', 100);
            $table->string('os', 100);
            $table->string('provider', 50)->nullable();
            $table->string('reference_no', 100);
            $table->tinyInteger('status')->comment('1=not subscribed, 2=request sent, 3=otp verified, 4=subscribed, 5=user unsubscribed	');
            $table->string('otp', 10)->nullable();
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
        Schema::dropIfExists('subscription_references');
    }
}
