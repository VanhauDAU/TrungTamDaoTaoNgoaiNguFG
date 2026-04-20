<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_room_members')) {
            return;
        }

        Schema::table('chat_room_members', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_room_members', 'hiddenAt')) {
                $table->timestamp('hiddenAt')->nullable()->after('lastSeenAt');
                $table->index(['chatRoomId', 'hiddenAt'], 'idx_chat_room_members_room_hidden');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('chat_room_members') || !Schema::hasColumn('chat_room_members', 'hiddenAt')) {
            return;
        }

        Schema::table('chat_room_members', function (Blueprint $table) {
            $table->dropIndex('idx_chat_room_members_room_hidden');
            $table->dropColumn('hiddenAt');
        });
    }
};
