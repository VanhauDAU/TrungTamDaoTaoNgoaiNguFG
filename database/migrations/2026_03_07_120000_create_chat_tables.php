<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_rooms')) {
            Schema::create('chat_rooms', function (Blueprint $table) {
                $table->bigIncrements('chatRoomId');
                $table->string('loai', 20)->comment('class_group | direct');
                $table->string('tenPhong', 150)->nullable();
                $table->integer('lopHocId')->nullable()->comment('FK -> lophoc.lopHocId');
                $table->string('matKhauHash', 255)->nullable();
                $table->integer('taoBoiId')->nullable()->comment('FK -> taikhoan.taiKhoanId');
                $table->unsignedBigInteger('lastMessageId')->nullable();
                $table->tinyInteger('trangThai')->default(1)->comment('0: inactive, 1: active, 2: archived');
                $table->timestamps();

                $table->unique('lopHocId', 'uq_chat_rooms_lopHocId');
                $table->index(['loai', 'trangThai'], 'idx_chat_rooms_loai_trangThai');
                $table->index('taoBoiId');
                $table->index('lastMessageId');
            });
        }

        if (!Schema::hasTable('chat_room_members')) {
            Schema::create('chat_room_members', function (Blueprint $table) {
                $table->bigIncrements('chatRoomMemberId');
                $table->unsignedBigInteger('chatRoomId')->comment('FK -> chat_rooms.chatRoomId');
                $table->integer('taiKhoanId')->comment('FK -> taikhoan.taiKhoanId');
                $table->string('vaiTro', 20)->default('member')->comment('member | teacher | owner');
                $table->timestamp('joinedAt')->nullable();
                $table->timestamp('joinedByPasswordAt')->nullable();
                $table->unsignedBigInteger('lastReadMessageId')->nullable();
                $table->timestamp('lastSeenAt')->nullable();
                $table->boolean('isMuted')->default(false);
                $table->timestamp('roiAt')->nullable();
                $table->timestamps();

                $table->unique(['chatRoomId', 'taiKhoanId'], 'uq_chat_room_members_room_user');
                $table->index('taiKhoanId');
                $table->index('lastReadMessageId');
                $table->index(['chatRoomId', 'roiAt'], 'idx_chat_room_members_room_roi');
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->bigIncrements('chatMessageId');
                $table->unsignedBigInteger('chatRoomId')->comment('FK -> chat_rooms.chatRoomId');
                $table->integer('nguoiGuiId')->comment('FK -> taikhoan.taiKhoanId');
                $table->unsignedBigInteger('replyToMessageId')->nullable()->comment('FK -> chat_messages.chatMessageId');
                $table->string('loai', 20)->default('text')->comment('text | image | file | location | system');
                $table->longText('noiDung')->nullable();
                $table->json('metaJson')->nullable();
                $table->timestamp('guiLuc')->useCurrent();
                $table->timestamp('deadlineThuHoi')->nullable();
                $table->timestamp('thuHoiLuc')->nullable();
                $table->timestamp('xoaLuc')->nullable();
                $table->timestamps();

                $table->index('chatRoomId');
                $table->index('nguoiGuiId');
                $table->index('replyToMessageId');
                $table->index('guiLuc');
                $table->index(['chatRoomId', 'chatMessageId'], 'idx_chat_messages_room_message');
            });
        }

        if (!Schema::hasTable('chat_message_attachments')) {
            Schema::create('chat_message_attachments', function (Blueprint $table) {
                $table->bigIncrements('chatAttachmentId');
                $table->unsignedBigInteger('chatMessageId')->comment('FK -> chat_messages.chatMessageId');
                $table->string('disk', 50)->default('public');
                $table->string('path', 500);
                $table->string('thumbnailPath', 500)->nullable();
                $table->string('tenGoc', 255);
                $table->string('mime', 100)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->timestamps();

                $table->index('chatMessageId');
            });
        }

        if (!Schema::hasTable('chat_message_reactions')) {
            Schema::create('chat_message_reactions', function (Blueprint $table) {
                $table->bigIncrements('chatReactionId');
                $table->unsignedBigInteger('chatMessageId')->comment('FK -> chat_messages.chatMessageId');
                $table->integer('taiKhoanId')->comment('FK -> taikhoan.taiKhoanId');
                $table->string('emoji', 50);
                $table->timestamps();

                $table->unique(['chatMessageId', 'taiKhoanId', 'emoji'], 'uq_chat_message_reactions_message_user_emoji');
                $table->index('taiKhoanId');
            });
        }

        if (!Schema::hasTable('chat_message_deletes')) {
            Schema::create('chat_message_deletes', function (Blueprint $table) {
                $table->bigIncrements('chatMessageDeleteId');
                $table->unsignedBigInteger('chatMessageId')->comment('FK -> chat_messages.chatMessageId');
                $table->integer('taiKhoanId')->comment('FK -> taikhoan.taiKhoanId');
                $table->timestamp('deletedAt')->useCurrent();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['chatMessageId', 'taiKhoanId'], 'uq_chat_message_deletes_message_user');
                $table->index('taiKhoanId');
            });
        }

        if (!Schema::hasTable('chat_audit_logs')) {
            Schema::create('chat_audit_logs', function (Blueprint $table) {
                $table->bigIncrements('chatAuditLogId');
                $table->unsignedBigInteger('chatRoomId')->nullable()->comment('FK -> chat_rooms.chatRoomId');
                $table->unsignedBigInteger('chatMessageId')->nullable()->comment('FK -> chat_messages.chatMessageId');
                $table->integer('taiKhoanId')->nullable()->comment('FK -> taikhoan.taiKhoanId');
                $table->string('hanhDong', 80);
                $table->json('duLieuCu')->nullable();
                $table->json('duLieuMoi')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('chatRoomId');
                $table->index('chatMessageId');
                $table->index('taiKhoanId');
                $table->index('hanhDong');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_audit_logs');
        Schema::dropIfExists('chat_message_deletes');
        Schema::dropIfExists('chat_message_reactions');
        Schema::dropIfExists('chat_message_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_room_members');
        Schema::dropIfExists('chat_rooms');
    }
};
