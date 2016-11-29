<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePulseSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pulse_sensors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->string('name');
            $table->string('slug');
            $table->string('cache_key_base');
            $table->string('field_type');
            $table->integer('ttl');
            $table->string('default_value');
            $table->integer('max');
            $table->integer('min');
            $table->string('reset_each');
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
        Schema::drop('pulse_sensors');
    }
}