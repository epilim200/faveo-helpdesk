<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('state')->nullable()->after('assigned_to');
            $table->string('district')->nullable()->after('state');
            $table->string('ips_type')->nullable()->after('district');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['state', 'district', 'ips_type']);
        });
    }
};
