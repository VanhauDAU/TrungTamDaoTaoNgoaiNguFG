<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use App\Models\Interaction\LienHe;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        // Danh sách cơ sở đang hoạt động
        $coSoDaoTao = CoSoDaoTao::with('tinhThanh')
            ->where('trangThai', 1)
            ->get();

        // Chỉ lấy tỉnh có cơ sở đang hoạt động
        $tinhThanhs = TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
            ->orderBy('tenTinhThanh')
            ->get();

        return view('clients.lien-he.index', compact('coSoDaoTao', 'tinhThanhs'));
    }

    /**
     * Xử lý đăng ký tư vấn miễn phí
     * Chỉ cần nhập 1 trong 2 trường: email hoặc số điện thoại
     */
    public function storeConsultation(Request $request)
    {
        // Validation tùy chỉnh: yêu cầu ít nhất 1 trong 2 (email hoặc sđt)
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'facility' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Kiểm tra ít nhất 1 trong 2 trường email hoặc phone phải có
        $validator->after(function ($validator) use ($request) {
            if (empty($request->email) && empty($request->phone)) {
                $validator->errors()->add('contact', 'Vui lòng cung cấp ít nhất email hoặc số điện thoại.');
            }
        });

        if ($validator->fails()) {
            // Nếu là AJAX request, trả về JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng kiểm tra lại thông tin!',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tạo nội dung chi tiết
        $noiDung = "Đăng ký tư vấn miễn phí\n";
        if ($request->course) {
            $noiDung .= "Khóa học quan tâm: " . $request->course . "\n";
        }
        if ($request->facility) {
            $noiDung .= "Cơ sở: " . $request->facility . "\n";
        }

        // Lưu vào database
        LienHe::create([
            'hoTen' => $request->fullname,
            'email' => $request->email,
            'soDienThoai' => $request->phone,
            'tieuDe' => 'Đăng ký tư vấn miễn phí',
            'noiDung' => $noiDung,
            'trangThai' => 0, // 0 = chưa xử lý
            'taiKhoanId' => auth()->check() ? auth()->id() : null,
        ]);

        // Nếu là AJAX request, trả về JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tư vấn thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất có thể.'
            ]);
        }

        return redirect()->back()->with('success', 'Đăng ký tư vấn thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất có thể.');
    }
}
