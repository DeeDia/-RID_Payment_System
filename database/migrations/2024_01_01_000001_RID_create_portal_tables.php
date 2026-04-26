<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────────────────────────────────────
// Migration: Create all tables for the International Payments Portal
// ─────────────────────────────────────────────────────────────────────────────

return new class extends Migration
{
    public function up(): void
    {
        // ── users ─────────────────────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 80);
            // id_number and account_number are unique and indexed for fast lookup
            $table->string('id_number', 13)->unique()->nullable();    // customers only
            $table->string('account_number', 16)->unique()->nullable(); // customers only
            $table->string('employee_id', 12)->unique()->nullable();   // employees only
            // bcrypt hash (60 chars); salt is embedded inside the hash by bcrypt
            $table->string('password');
            $table->enum('role', ['customer', 'employee'])->default('customer');
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });

        // ── transactions ──────────────────────────────────────────────────
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            // Decimal precision to avoid floating-point issues with money
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);    // ISO 4217 code
            $table->string('provider', 10); // SWIFT / SEPA / WIRE
            // Beneficiary details — HTML-stripped before storage
            $table->string('beneficiary_account', 34);
            $table->string('beneficiary_name', 100);
            // SWIFT/BIC code: ISO 9362 — 8 or 11 chars
            $table->string('swift_code', 11);
            $table->enum('status', ['pending', 'verified', 'submitted_to_swift'])->default('pending');
            // Employee who verified this transaction
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_id');
        });

        // ── audit_logs (append-only) ──────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role', 10);
            $table->string('action', 50);       // e.g. TRANSACTION_VERIFIED
            $table->unsignedBigInteger('subject_id')->nullable(); // e.g. transaction ID
            $table->string('ip_address', 45);   // IPv6 max length
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — audit logs are immutable

            $table->index(['actor_id', 'created_at']);
            $table->index('action');
        });

        // ── sessions ──────────────────────────────────────────────────────
        // Using database sessions for server-side revocation capability
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
