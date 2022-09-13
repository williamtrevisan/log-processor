<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->uuid('consumer_id');
            $table->uuid('service_id');
            $table->string('service_name');
            $table->string('method');
            $table->string('uri');
            $table->string('url');
            $table->smallInteger('size');
            $table->smallInteger('response_status');
            $table->smallInteger('proxy_latency');
            $table->smallInteger('kong_latency');
            $table->smallInteger('request_latency');
            $table->ipAddress('client_ip');
            $table->timestamp('started_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
