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
        Schema::table('notifications', function (Blueprint $table) { // Assuming your table is named 'notifications'
            $table->string('type')->after('message');
            $table->unsignedBigInteger('notifiable_id')->after('type');
            $table->string('notifiable_type')->after('notifiable_id');
            $table->json('data')->after('notifiable_type');
            $table->timestamp('read_at')->nullable()->after('data');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'notifiable_id', 'notifiable_type', 'data', 'read_at']);
        });
    }
};
