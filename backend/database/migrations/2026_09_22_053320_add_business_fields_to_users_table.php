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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->unique()->nullable()->after('email_verified_at');
            $table->string('phone')->unique()->nullable()->after('google_id');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->string('ktp_file_path')->nullable()->after('role');
            $table->enum('ktp_status', ['not_submitted', 'pending_review', 'approved', 'rejected'])->default('not_submitted')->after('ktp_file_path');
            $table->timestamp('ktp_submitted_at')->nullable()->after('ktp_status');
            $table->timestamp('ktp_reviewed_at')->nullable()->after('ktp_submitted_at');
            $table->foreignId('ktp_reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('ktp_reviewed_at');
            $table->text('ktp_rejection_reason')->nullable()->after('ktp_reviewed_by');
            
            $table->string('password')->nullable()->change();

            $table->index('ktp_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ktp_reviewed_by']);
            $table->dropIndex(['ktp_status']);
            $table->dropColumn([
                'google_id',
                'phone',
                'phone_verified_at',
                'role',
                'ktp_file_path',
                'ktp_status',
                'ktp_submitted_at',
                'ktp_reviewed_at',
                'ktp_reviewed_by',
                'ktp_rejection_reason'
            ]);
            $table->string('password')->nullable(false)->change();
        });
    }
};
