<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(Role::WARGA->value)->after('password');
            $table->string('divisi')->nullable()->after('role');
            $table->foreignId('warga_id')->nullable()->after('divisi')->constrained('warga')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warga_id');
            $table->dropColumn(['role', 'divisi']);
        });
    }
};
