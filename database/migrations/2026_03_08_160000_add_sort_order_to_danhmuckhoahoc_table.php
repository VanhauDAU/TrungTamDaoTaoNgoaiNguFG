<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('danhmuckhoahoc', 'sort_order')) {
            Schema::table('danhmuckhoahoc', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
            });
        }

        $groups = DB::table('danhmuckhoahoc')
            ->select('parent_id')
            ->groupBy('parent_id')
            ->pluck('parent_id');

        foreach ($groups as $parentId) {
            $items = DB::table('danhmuckhoahoc')
                ->where('parent_id', $parentId)
                ->orderBy('tenDanhMuc')
                ->orderBy('danhMucId')
                ->get(['danhMucId']);

            foreach ($items as $index => $item) {
                DB::table('danhmuckhoahoc')
                    ->where('danhMucId', $item->danhMucId)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('danhmuckhoahoc', 'sort_order')) {
            Schema::table('danhmuckhoahoc', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
