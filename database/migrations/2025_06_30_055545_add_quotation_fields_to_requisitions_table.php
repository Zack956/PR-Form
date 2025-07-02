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
        Schema::table('requisitions', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('user_id')->constrained();
            $table->string('quotation_number')->nullable()->after('vendor_id');
            $table->string('quotation_file_path')->nullable()->after('quotation_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            //
        });
    }
};
