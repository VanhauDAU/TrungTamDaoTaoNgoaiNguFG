<?php

namespace App\Http\Controllers\Admin\TaiChinh;

use App\Contracts\Admin\TaiChinh\HoaDonServiceInterface;
use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HoaDonController extends Controller
{
    public function __construct(
        protected HoaDonServiceInterface $hoaDonService,
        protected FinanceDocumentService $financeDocumentService
        )
    {
        $this->middleware('permission:tai_chinh,xem')->only('index', 'show', 'printInvoice', 'printReceipt');
        $this->middleware('permission:tai_chinh,sua')->only('update', 'storePhieuThu', 'destroyPhieuThu', 'emailInvoice', 'emailReceipt');
    }

    public function index(Request $request)
    {
        return view('admin.hoa-don.index', $this->hoaDonService->getList($request));
    }

    public function show(int $id)
    {
        return view('admin.hoa-don.show', $this->hoaDonService->getDetail($id));
    }

    public function update(Request $request, int $id)
    {
        $this->hoaDonService->update($request, $id);
        return redirect()->route('admin.hoa-don.show', $id)
            ->with('success', 'Đã cập nhật hóa đơn thành công.');
    }

    public function storePhieuThu(Request $request, int $hoaDonId)
    {
        $phieuThu = $this->hoaDonService->storePhieuThu($request, $hoaDonId);

        $redirect = redirect()->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã tạo phiếu thu thành công.');

        if ($request->input('afterAction') === 'print') {
            $redirect->with('autoPrintReceiptId', $phieuThu->phieuThuId);
        }

        return $redirect;
    }

    public function destroyPhieuThu(int $id)
    {
        $hoaDonId = $this->hoaDonService->destroyPhieuThu($id);
        return redirect()->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã hủy phiếu thu thành công.');
    }

    public function printInvoice(int $id)
    {
        return $this->financeDocumentService->streamInvoicePdf(
            $this->financeDocumentService->findInvoiceForAdmin($id)
        );
    }

    public function printReceipt(int $id)
    {
        return $this->financeDocumentService->streamReceiptPdf(
            $this->financeDocumentService->findReceiptForAdmin($id)
        );
    }

    public function emailInvoice(Request $request, int $id)
    {
        $invoice = $this->financeDocumentService->findInvoiceForAdmin($id);
        [$email, $message] = $this->validateEmailPayload(
            $request,
            $this->financeDocumentService->defaultInvoiceEmail($invoice)
        );

        $this->financeDocumentService->sendInvoiceEmail($invoice, $email, $message);

        return back()->with('success', 'Đã gửi email hóa đơn thành công.');
    }

    public function emailReceipt(Request $request, int $id)
    {
        $receipt = $this->financeDocumentService->findReceiptForAdmin($id);
        [$email, $message] = $this->validateEmailPayload(
            $request,
            $this->financeDocumentService->defaultReceiptEmail($receipt)
        );

        $this->financeDocumentService->sendReceiptEmail($receipt, $email, $message);

        return back()->with('success', 'Đã gửi email phiếu thu thành công.');
    }

    private function validateEmailPayload(Request $request, ?string $fallbackEmail): array
    {
        $data = $request->validate([
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        $email = $data['email'] ?? $fallbackEmail;

        if (!$email) {
            throw ValidationException::withMessages([
                'email' => ['Không có địa chỉ email nhận tài liệu. Vui lòng nhập email thủ công.'],
            ]);
        }

        return [$email, $data['message'] ?? null];
    }
}
