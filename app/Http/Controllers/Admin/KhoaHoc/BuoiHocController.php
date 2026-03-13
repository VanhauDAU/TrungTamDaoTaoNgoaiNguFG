<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\BuoiHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuoiHocController extends Controller
{
    public function __construct(
        protected BuoiHocServiceInterface $buoiHocService
    ) {}

    public function store(Request $request)
    {
        $result = $this->buoiHocService->store($request);

        return redirect()
            ->route('admin.lop-hoc.show', $result['lopHocSlug'])
            ->with($result['flashType'], $result['message']);
    }

    public function update(Request $request, int $id)
    {
        $result = $this->buoiHocService->update($request, $id);

        if ($request->wantsJson()) {
            return response()->json($result['json']);
        }

        return redirect()
            ->route('admin.lop-hoc.show', $result['lopHocSlug'])
            ->with('success', $result['message']);
    }

    public function destroy(int $id)
    {
        $result = $this->buoiHocService->destroy($id);

        if (request()->wantsJson()) {
            return response()->json($result['json']);
        }

        return redirect()
            ->route('admin.lop-hoc.show', $result['lopHocSlug'])
            ->with('success', $result['message']);
    }

    public function autoGenerate(Request $request, int $lopHocId)
    {
        $result = $this->buoiHocService->autoGenerate($request, $lopHocId);

        return redirect()
            ->route('admin.lop-hoc.show', $result['lopHocSlug'])
            ->with($result['flashType'], $result['message']);
    }
}
