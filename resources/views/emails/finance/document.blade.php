<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>{{ ucfirst($documentLabel) }} {{ $code }}</title>
</head>
<body style="margin:0;padding:24px;font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;border:1px solid #e5e7eb;">
        <p style="margin:0 0 16px;font-size:15px;">Xin chào {{ $recipientName }},</p>
        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
            Trung tâm gửi đến bạn <strong>{{ $documentLabel }}</strong> <strong>{{ $code }}</strong>.
            File được đính kèm trong email này để bạn tiện lưu trữ, in hoặc đối soát.
        </p>

        @if ($note)
            <div style="margin:0 0 16px;padding:16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <div style="font-weight:700;margin-bottom:6px;">Ghi chú</div>
                <div style="font-size:14px;line-height:1.6;">{{ $note }}</div>
            </div>
        @endif

        <p style="margin:0;font-size:14px;line-height:1.6;color:#4b5563;">
            Nếu bạn cần hỗ trợ thêm về học phí hoặc đối chiếu thanh toán, vui lòng liên hệ trung tâm.
        </p>
    </div>
</body>
</html>
