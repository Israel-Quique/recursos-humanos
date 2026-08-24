<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometrico_dispositivos', function (Blueprint $table) {
            if (! Schema::hasColumn('biometrico_dispositivos', 'last_synced_mark_at')) {
                $table->timestamp('last_synced_mark_at')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('biometrico_dispositivos', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('last_synced_mark_at');
            }

            if (! Schema::hasColumn('biometrico_dispositivos', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_seen_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('biometrico_dispositivos', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('biometrico_dispositivos', 'last_error')) {
                $columns[] = 'last_error';
            }

            if (Schema::hasColumn('biometrico_dispositivos', 'last_seen_at')) {
                $columns[] = 'last_seen_at';
            }

            if (Schema::hasColumn('biometrico_dispositivos', 'last_synced_mark_at')) {
                $columns[] = 'last_synced_mark_at';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
