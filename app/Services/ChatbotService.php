<?php

namespace App\Services;

use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Finance\HoaDon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Xây dựng ngữ cảnh thông tin học viên từ DB để inject vào prompt AI
     */
    public function buildContext($user): string
    {
        $hoSo = $user->hoSoNguoiDung;
        $tenHocVien = $hoSo?->hoTen ?? $user->email;
        $today = Carbon::today();

        // ── 1. Lớp học đang hoạt động ─────────────────────────
        $dangKys = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('trangThai', [1, 2]) // chờ = 1, xác nhận = 2
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lopHoc.buoiHocs.caHoc',
            ])
            ->get();

        $lopHocLines = [];
        foreach ($dangKys as $dk) {
            $lop = $dk->lopHoc;
            if (!$lop) continue;

            $tenKhoa = $lop->khoaHoc?->tenKhoaHoc ?? 'Không rõ';
            $tenLop  = $lop->tenLopHoc ?? $lop->maLopHoc ?? 'Không rõ';
            $coSo    = $lop->coSo?->tenCoSo ?? 'Không rõ';
            $giaoVien = $lop->taiKhoan?->hoSoNguoiDung?->hoTen ?? 'Chưa phân công';
            $trangThaiDK = match ((int) $dk->trangThai) {
                1 => 'Chờ xác nhận',
                2 => 'Đang học',
                default => 'Không xác định',
            };

            // Lịch học (buổi sắp tới trong 7 ngày)
            $buoiSapToi = $lop->buoiHocs
                ->where('ngayHoc', '>=', $today->toDateString())
                ->where('ngayHoc', '<=', $today->copy()->addDays(7)->toDateString())
                ->sortBy('ngayHoc')
                ->values();

            $lichHoc = $buoiSapToi->map(function ($buoi) {
                $ngay = Carbon::parse($buoi->ngayHoc)->isoFormat('dddd, DD/MM/YYYY');
                $ca   = $buoi->caHoc?->tenCaHoc ?? ($buoi->caHoc ? "{$buoi->caHoc->gioBatDau}–{$buoi->caHoc->gioKetThuc}" : 'Không rõ ca');
                return "  - {$ngay}, {$ca}";
            })->implode("\n");

            $lopHocLines[] = "• Lớp: {$tenLop} (Khóa: {$tenKhoa})\n"
                . "  Cơ sở: {$coSo} | Giáo viên: {$giaoVien} | Trạng thái: {$trangThaiDK}\n"
                . ($lichHoc ? "  Buổi học sắp tới (7 ngày):\n{$lichHoc}" : "  Không có buổi học trong 7 ngày tới");
        }

        $lopHocText = $lopHocLines
            ? implode("\n\n", $lopHocLines)
            : 'Hiện chưa đăng ký lớp học nào.';

        // ── 2. Hóa đơn còn nợ ─────────────────────────────────
        $hoaDons = HoaDon::where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('trangThai', [0, 1]) // chưa TT hoặc 1 phần
            ->with('dangKyLopHoc.lopHoc.khoaHoc')
            ->orderBy('ngayLap', 'desc')
            ->get();

        $hoaDonLines = [];
        foreach ($hoaDons as $hd) {
            $maHD    = $hd->maHoaDon ?? "HD-{$hd->hoaDonId}";
            $tenLop  = $hd->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Không rõ';
            $conNo   = number_format($hd->conNo, 0, ',', '.') . ' VNĐ';
            $hanTT   = $hd->ngayHetHan ? Carbon::parse($hd->ngayHetHan)->format('d/m/Y') : 'Không có hạn';
            $ttLabel = $hd->trangThaiLabel;
            $hoaDonLines[] = "• {$maHD} – Lớp: {$tenLop} | Còn nợ: {$conNo} | Hạn TT: {$hanTT} | {$ttLabel}";
        }

        $hoaDonText = $hoaDonLines
            ? implode("\n", $hoaDonLines)
            : 'Không có hóa đơn nào còn nợ. Đã thanh toán đầy đủ!';

        // ── 3. Tổng hợp ───────────────────────────────────────
        $ngayHom = $today->isoFormat('dddd, DD/MM/YYYY');

        return <<<CONTEXT
        THÔNG TIN HỌC VIÊN:
        - Tên: {$tenHocVien}
        - Email: {$user->email}
        - Ngày hôm nay: {$ngayHom}

        LỚP HỌC ĐANG THEO HỌC:
        {$lopHocText}

        HÓA ĐƠN CÒN NỢ:
        {$hoaDonText}
        CONTEXT;
    }

    /**
     * Gửi tin nhắn đến Gemini API và nhận phản hồi
     */
    public function chat(string $userMessage, string $context): string
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            return '⚠️ Chatbot chưa được cấu hình. Vui lòng liên hệ ban quản trị.';
        }

        $systemPrompt = <<<PROMPT
        Bạn là trợ lý AI thân thiện của Trung tâm Ngoại ngữ FiveGenius, hỗ trợ học viên 24/7.
        Hãy trả lời bằng tiếng Việt, ngắn gọn, thân thiện và chuyên nghiệp.
        Sử dụng thông tin học viên bên dưới để trả lời chính xác.
        Nếu câu hỏi không liên quan đến thông tin bạn có, hãy hướng dẫn học viên liên hệ nhân viên trực tiếp.
        Không bịa đặt thông tin không có trong ngữ cảnh.

        {$context}
        PROMPT;

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userMessage]]],
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::timeout(20)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                    $payload
                );

            if ($response->failed()) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return '❌ Xin lỗi, hiện tại tôi đang gặp sự cố. Vui lòng thử lại sau ít phút.';
            }

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text']
                ?? 'Tôi chưa hiểu câu hỏi này. Bạn có thể hỏi lại theo cách khác không?';

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini connection timeout', ['error' => $e->getMessage()]);
            return '⏱️ Kết nối đến AI bị timeout. Vui lòng thử lại sau.';
        } catch (\Exception $e) {
            Log::error('Chatbot unexpected error', ['error' => $e->getMessage()]);
            return '❌ Đã xảy ra lỗi không mong muốn. Vui lòng liên hệ nhân viên hỗ trợ.';
        }
    }
}
