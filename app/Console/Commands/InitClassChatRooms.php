<?php

namespace App\Console\Commands;

use App\Services\ChatRoomService;
use Illuminate\Console\Command;

class InitClassChatRooms extends Command
{
    protected $signature = 'chat:init-class-rooms {--dry-run : Xem trước kết quả mà không ghi dữ liệu}';
    protected $description = 'Khởi tạo room chat nhóm cho các lớp học hiện có';

    public function handle(ChatRoomService $chatRoomService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = $chatRoomService->bootstrapClassRooms($dryRun);

        $this->info($dryRun ? '[DRY-RUN] Kiem tra du lieu room chat lop hoc' : 'Khoi tao room chat lop hoc');
        $this->newLine();
        $this->table(
            ['Tong lop', 'Room moi', 'Room da co', 'Thanh vien giao vien duoc dong bo'],
            [[
                $stats['totalClasses'],
                $stats['createdRooms'],
                $stats['existingRooms'],
                $stats['teacherMembersCreatedOrUpdated'],
            ]]
        );

        $this->newLine();
        $this->line($dryRun
            ? 'Khong co du lieu nao duoc ghi vao database.'
            : 'Hoan tat khoi tao room chat lop hoc.');

        return self::SUCCESS;
    }
}
