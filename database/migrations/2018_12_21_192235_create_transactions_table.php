<?php

use Illuminate\Support\Facades\Schema;
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
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id');
            $table->string('code');
            $table->string('narration');
            $table->timestamps();
        });

        Schema::create('member_service', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->integer('member_id');
            $table->string('reference'
            );
        });

        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('country_code')->nullable();
            $table->string('phone_number')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('identities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id');
            $table->string('type');
            $table->string('number')->unique();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->nullable();
            $table->integer('service_id')->nullable();
            $table->string('type');
            $table->string('narration');
            $table->string('reference');
            $table->string('service_reference');
            $table->decimal('amount', 14, 2);
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
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('services');
        Schema::dropIfExists('members');
        Schema::dropIfExists('member_service');
        Schema::dropIfExists('identities');
    }
}
