<?php

namespace App\Http\Controllers\Admin\CoSo;

use App\Contracts\Admin\CoSo\CoSoServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoSoController extends Controller
{
    public function __construct(
        protected CoSoServiceInterface $coSoService
        )
    {
    }

    public function index(Request $request)
    {
        return view('admin.co-so.index', $this->coSoService->getList($request));
    }

    public function create()
    {
        return view('admin.co-so.create', $this->coSoService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $coSo = $this->coSoService->store($request);
        return redirect()->route('admin.co-so.index')
            ->with('success', 'Đã thêm cơ sở «' . $coSo->tenCoSo . '» thành công.');
    }

    public function show(int $id)
    {
        return view('admin.co-so.show', $this->coSoService->getDetail($id));
    }

    public function operationalSnapshot(int $id)
    {
        return response()->json([
            'success' => true,
            'snapshot' => $this->coSoService->getOperationalSnapshot($id),
        ]);
    }

    public function edit(int $id)
    {
        return view('admin.co-so.edit', $this->coSoService->getEditFormData($id));
    }

    public function update(Request $request, int $id)
    {
        $coSo = $this->coSoService->update($request, $id);
        return redirect()->route('admin.co-so.index')
            ->with('success', 'Đã cập nhật cơ sở «' . $coSo->tenCoSo . '» thành công.');
    }

    public function destroy(int $id)
    {
        try {
            $ten = $this->coSoService->destroy($id);
            return redirect()->route('admin.co-so.index')->with('success', "Đã xóa cơ sở «{$ten}».");
        }
        catch (\RuntimeException $e) {
            return redirect()->route('admin.co-so.index')->with('error', $e->getMessage());
        }
    }

    // ─── API ────────────────────────────────────────────────────────────────

    public function getPhuongXa(int $maTinh)
    {
        return response()->json($this->coSoService->getPhuongXa($maTinh));
    }

    public function apiList(Request $request)
    {
        return response()->json(['success' => true, 'coSos' => $this->coSoService->apiList($request)]);
    }

    public function getPhuongXaCoCoSo(int $tinhThanhId)
    {
        return response()->json(['success' => true, 'phuongXas' => $this->coSoService->getPhuongXaCoCoSo($tinhThanhId)]);
    }

    public function getCoSoByLocation(Request $request)
    {
        return response()->json(['success' => true, 'coSos' => $this->coSoService->getCoSoByLocation($request)]);
    }
}
