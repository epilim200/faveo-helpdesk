<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 2)->unique();
            $table->string('name');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 2);
            $table->string('name');
            $table->integer('state_id')->unsigned();
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('ips_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sort')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('states');
        Schema::dropIfExists('ips_types');
    }
};
