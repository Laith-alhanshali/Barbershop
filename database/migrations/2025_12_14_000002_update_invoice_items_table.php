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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('service_id')->nullable()->after('invoice_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('duration_min')->default(0)->after('qty');
            $table->softDeletes();
        });

        // Rename columns to match requirements
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('description', 'name');
            $table->renameColumn('total', 'line_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('name', 'description');
            $table->renameColumn('line_total', 'total');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'duration_min']);
        });
    }
};
