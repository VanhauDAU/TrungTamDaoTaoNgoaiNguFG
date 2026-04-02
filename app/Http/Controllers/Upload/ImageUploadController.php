<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Services\Support\Uploads\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImageUploadController extends Controller
{
    public function store(Request $request, ImageUploadService $imageUploadService)
    {
        $request->validate([
            'preset' => ['required', 'string', Rule::in($imageUploadService->presetNames())],
            'file' => ['required', 'file'],
        ], [
            'preset.required' => 'Thiếu cấu hình upload.',
            'preset.in' => 'Cấu hình upload không hợp lệ.',
        ]);

        $file = $imageUploadService->validateAndStore(
            $request,
            (string) $request->input('preset'),
            'file'
        );

        return response()->json([
            'message' => 'Tải ảnh lên thành công.',
            'file' => $file,
        ]);
    }
}
