<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('biteship');
            $table->string('biteship_order_id')->nullable()->index();
            $table->string('biteship_tracking_id')->nullable()->index();
            $table->string('waybill_id')->nullable()->index();
            $table->string('courier_code')->default('jne');
            $table->string('courier_service')->default('reg');
            $table->string('courier_name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('tracking_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('history')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
