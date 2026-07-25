<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->unsignedTinyInteger('discount_percent');
            $table->date('expires_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_token')->nullable()->unique();
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('voucher_code', 50)->nullable();
            $table->decimal('voucher_discount_amount', 15, 2)->default(0);
            $table->timestamp('payment_due_at')->nullable();
        });

        DB::table('orders')->update([
            'subtotal_amount' => DB::raw('total_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropUnique(['checkout_token']);
            $table->dropColumn([
                'checkout_token',
                'subtotal_amount',
                'voucher_code',
                'voucher_discount_amount',
                'payment_due_at',
            ]);
        });

        Schema::dropIfExists('vouchers');
    }
};
