<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\CaHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaHocController extends Controller
{
    public function __construct(
        protected CaHocServiceInterface $caHocService
        )
    {
    }

    public function index(Request $request)
    {
        return view('admin.ca-hoc.index', $this->caHocService->getList($request));
    }

    public function store(Request $request)
    {
        $result = $this->caHocService->store($request);

        return response()->json(
            array_diff_key($result, ['status' => true]),
            $result['status']
        );
    }

    public function update(Request $request, int $id)
    {
        $result = $this->caHocService->update($request, $id);

        return response()->json(
            array_diff_key($result, ['status' => true]),
            $result['status']
        );
    }

    public function destroy(int $id)
    {
        $result = $this->caHocService->destroy($id);

        return response()->json(
            array_diff_key($result, ['status' => true]),
            $result['status']
        );
    }

    public function toggleStatus(int $id)
    {
        $result = $this->caHocService->toggleStatus($id);

        return response()->json(
            array_diff_key($result, ['status' => true]),
            $result['status']
        );
    }
}