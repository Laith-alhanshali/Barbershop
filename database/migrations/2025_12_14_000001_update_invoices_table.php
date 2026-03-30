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
        Schema::table('invoices', function (Blueprint $table) {
            // Add new columns
            $table->string('number')->unique()->after('id');
            $table->foreignId('customer_id')->nullable()->after('appointment_id')->constrained()->nullOnDelete();
            $table->foreignId('barber_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('paid_at');
            $table->softDeletes();
        });

        // Rename columns to match requirements
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('grand_total', 'total');
            $table->renameColumn('payment_status', 'status');
            $table->renameColumn('issued_by', 'created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('total', 'grand_total');
            $table->renameColumn('status', 'payment_status');
            $table->renameColumn('created_by', 'issued_by');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['barber_id']);
            $table->dropColumn(['number', 'customer_id', 'barber_id', 'notes']);
        });
    }
};
