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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->after('proof_image');
            $table->string('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->timestamp('xendit_expiry_date')->nullable()->after('xendit_invoice_url');
            $table->string('xendit_payment_method')->nullable()->after('xendit_expiry_date');
            $table->string('xendit_payment_channel')->nullable()->after('xendit_payment_method');
            $table->timestamp('xendit_paid_at')->nullable()->after('xendit_payment_channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'xendit_invoice_id',
                'xendit_invoice_url',
                'xendit_expiry_date',
                'xendit_payment_method',
                'xendit_payment_channel',
                'xendit_paid_at',
            ]);
        });
    }
};
