<?php

namespace Tests\Feature;

use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Interaction\Chat\ChatMessage;
use App\Models\Interaction\Chat\ChatMessageAttachment;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatPhaseOneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createMinimalChatDependencies();
    }

    public function test_student_can_send_image_attachment_in_direct_room(): void
    {
        Storage::fake('public');

        $student = $this->createAccount('hocvien_a', 'Học viên A');
        $peer = $this->createAccount('hocvien_b', 'Học viên B');
        $room = $this->createDirectRoom($student, $peer);

        $response = $this->actingAs($student)->post(route('home.api.chat.send'), [
            'roomId' => $room->chatRoomId,
            'message' => '',
            'attachments' => [
                UploadedFile::fake()->image('bai-tap.png', 320, 240),
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('chatMessage.type', 'image')
            ->assertJsonPath('chatMessage.attachments.0.name', 'bai-tap.png');

        $attachmentPath = $response->json('chatMessage.attachments.0.url');
        $attachment = ChatMessageAttachment::query()->firstOrFail();

        $this->assertDatabaseCount('chat_messages', 1);
        $this->assertDatabaseCount('chat_message_attachments', 1);
        $this->assertStringContainsString('/api/chat/attachments/', $attachmentPath);

        $this->actingAs($student)
            ->get(route('home.api.chat.attachments.view', ['id' => $attachment->chatAttachmentId]))
            ->assertOk();
    }

    public function test_user_can_download_file_attachment_from_chat_message(): void
    {
        Storage::fake('public');

        $student = $this->createAccount('hocvien_a', 'Học viên A');
        $peer = $this->createAccount('hocvien_b', 'Học viên B');
        $room = $this->createDirectRoom($student, $peer);

        $response = $this->actingAs($student)->post(route('home.api.chat.send'), [
            'roomId' => $room->chatRoomId,
            'message' => '',
            'attachments' => [
                UploadedFile::fake()->create('tai-lieu.txt', 12, 'text/plain'),
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('chatMessage.type', 'file')
            ->assertJsonPath('chatMessage.attachments.0.name', 'tai-lieu.txt');

        $attachment = ChatMessageAttachment::query()->firstOrFail();

        $downloadResponse = $this->actingAs($student)
            ->get(route('home.api.chat.attachments.download', ['id' => $attachment->chatAttachmentId]));

        $downloadResponse->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=tai-lieu.txt',
            (string) $downloadResponse->headers->get('content-disposition')
        );
    }

    public function test_user_can_delete_message_for_me_and_message_disappears_from_history(): void
    {
        $student = $this->createAccount('hocvien_a', 'Học viên A');
        $peer = $this->createAccount('hocvien_b', 'Học viên B');
        $room = $this->createDirectRoom($student, $peer);

        $message = ChatMessage::query()->create([
            'chatRoomId' => $room->chatRoomId,
            'nguoiGuiId' => $peer->taiKhoanId,
            'loai' => ChatMessage::TYPE_TEXT,
            'noiDung' => 'Tin nhắn cần ẩn khỏi người xem hiện tại',
            'guiLuc' => now(),
            'deadlineThuHoi' => now()->addDay(),
        ]);

        $room->forceFill([
            'lastMessageId' => $message->chatMessageId,
            'updated_at' => now(),
        ])->save();

        $deleteResponse = $this->actingAs($student)->post(
            route('home.api.chat.delete-for-me', ['id' => $message->chatMessageId]),
            [
                'roomId' => $room->chatRoomId,
            ]
        );

        $deleteResponse->assertOk();

        $this->assertDatabaseHas('chat_message_deletes', [
            'chatMessageId' => $message->chatMessageId,
            'taiKhoanId' => $student->taiKhoanId,
        ]);

        $messagesResponse = $this->actingAs($student)->getJson(
            route('home.api.chat.messages', ['id' => $room->chatRoomId])
        );

        $messagesResponse->assertOk();
        $this->assertNotContains(
            $message->chatMessageId,
            collect($messagesResponse->json('messages'))->pluck('id')->all()
        );
    }

    private function createMinimalChatDependencies(): void
    {
        foreach ([
            'chat_audit_logs',
            'chat_message_deletes',
            'chat_message_reactions',
            'chat_message_attachments',
            'chat_messages',
            'chat_room_members',
            'chat_rooms',
            'dangKyLopHoc',
            'lophoc',
            'hosonguoidung',
            'taikhoan',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->string('email')->nullable();
            $table->string('matKhau')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_HOC_VIEN);
            $table->integer('nhomQuyenId')->nullable();
            $table->integer('trangThai')->default(1);
            $table->rememberToken();
            $table->timestamp('lastLogin')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->increments('lopHocId');
            $table->integer('khoaHocId')->nullable();
            $table->string('tenLopHoc')->nullable();
            $table->integer('taiKhoanId')->nullable();
            $table->integer('trangThai')->default(4);
            $table->string('lichHoc')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('dangKyLopHoc', function (Blueprint $table) {
            $table->increments('dangKyLopHocId');
            $table->integer('taiKhoanId');
            $table->integer('lopHocId');
            $table->date('ngayDangKy')->nullable();
            $table->integer('trangThai')->default(1);
        });

        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->bigIncrements('chatRoomId');
            $table->string('loai', 20);
            $table->string('tenPhong', 150)->nullable();
            $table->integer('lopHocId')->nullable();
            $table->string('matKhauHash', 255)->nullable();
            $table->integer('taoBoiId')->nullable();
            $table->unsignedBigInteger('lastMessageId')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('chat_room_members', function (Blueprint $table) {
            $table->bigIncrements('chatRoomMemberId');
            $table->unsignedBigInteger('chatRoomId');
            $table->integer('taiKhoanId');
            $table->string('vaiTro', 20)->default('member');
            $table->timestamp('joinedAt')->nullable();
            $table->timestamp('joinedByPasswordAt')->nullable();
            $table->unsignedBigInteger('lastReadMessageId')->nullable();
            $table->timestamp('lastSeenAt')->nullable();
            $table->boolean('isMuted')->default(false);
            $table->timestamp('roiAt')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->bigIncrements('chatMessageId');
            $table->unsignedBigInteger('chatRoomId');
            $table->integer('nguoiGuiId');
            $table->unsignedBigInteger('replyToMessageId')->nullable();
            $table->string('loai', 20)->default('text');
            $table->longText('noiDung')->nullable();
            $table->json('metaJson')->nullable();
            $table->timestamp('guiLuc')->nullable();
            $table->timestamp('deadlineThuHoi')->nullable();
            $table->timestamp('thuHoiLuc')->nullable();
            $table->timestamp('xoaLuc')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_message_attachments', function (Blueprint $table) {
            $table->bigIncrements('chatAttachmentId');
            $table->unsignedBigInteger('chatMessageId');
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('thumbnailPath', 500)->nullable();
            $table->string('tenGoc', 255);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_message_reactions', function (Blueprint $table) {
            $table->bigIncrements('chatReactionId');
            $table->unsignedBigInteger('chatMessageId');
            $table->integer('taiKhoanId');
            $table->string('emoji', 50);
            $table->timestamps();
        });

        Schema::create('chat_message_deletes', function (Blueprint $table) {
            $table->bigIncrements('chatMessageDeleteId');
            $table->unsignedBigInteger('chatMessageId');
            $table->integer('taiKhoanId');
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('chat_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('chatAuditLogId');
            $table->unsignedBigInteger('chatRoomId')->nullable();
            $table->unsignedBigInteger('chatMessageId')->nullable();
            $table->integer('taiKhoanId')->nullable();
            $table->string('hanhDong', 80);
            $table->json('duLieuCu')->nullable();
            $table->json('duLieuMoi')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    private function createAccount(string $username, string $fullName): TaiKhoan
    {
        $account = TaiKhoan::query()->create([
            'taiKhoan' => $username,
            'email' => $username . '@example.test',
            'matKhau' => bcrypt('secret'),
            'role' => TaiKhoan::ROLE_HOC_VIEN,
            'trangThai' => 1,
        ]);

        HoSoNguoiDung::query()->create([
            'taiKhoanId' => $account->taiKhoanId,
            'hoTen' => $fullName,
        ]);

        return $account;
    }

    private function createDirectRoom(TaiKhoan $first, TaiKhoan $second): ChatRoom
    {
        $room = ChatRoom::query()->create([
            'loai' => ChatRoom::TYPE_DIRECT,
            'taoBoiId' => $first->taiKhoanId,
            'trangThai' => ChatRoom::STATUS_ACTIVE,
        ]);

        foreach ([$first, $second] as $account) {
            ChatRoomMember::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'taiKhoanId' => $account->taiKhoanId,
                'vaiTro' => ChatRoomMember::ROLE_MEMBER,
                'joinedAt' => now(),
            ]);
        }

        return $room;
    }
}
