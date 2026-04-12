<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CitizenLookupService
{
    public function lookupCitizen(string $cccd, string $fullName): array
    {
        $normalizedLegalId = $this->normalizeLegalId($cccd);
        $normalizedLegalName = $this->normalizeLegalName($fullName);

        $response = Http::acceptJson()
            ->timeout((int) config('services.vietqr.timeout', 8))
            ->connectTimeout((int) config('services.vietqr.connect_timeout', 5))
            ->withHeaders([
                'x-client-id' => (string) config('services.vietqr.client_id'),
                'x-api-key' => (string) config('services.vietqr.api_key'),
            ])
            ->post((string) config('services.vietqr.citizen_url'), [
                'legalId' => $normalizedLegalId,
                'legalName' => $normalizedLegalName,
            ]);

        if ($response->status() === 429) {
            return $this->buildResult(
                success: false,
                status: 'rate_limited',
                message: 'Dịch vụ tra cứu đang quá tải. Vui lòng chờ một chút rồi thử lại.'
            );
        }

        if ($response->failed()) {
            return $this->buildResult(
                success: false,
                status: 'error',
                message: 'Không thể kết nối dịch vụ đối chiếu CCCD lúc này.'
            );
        }

        $payload = $response->json();
        $code = (string) data_get($payload, 'code', '');
        $desc = (string) data_get($payload, 'desc', '');
        $resolvedName = trim((string) (data_get($payload, 'data.name') ?: data_get($payload, 'data.legalName') ?: ''));
        $resolvedLegalId = trim((string) (data_get($payload, 'data.taxId') ?: data_get($payload, 'data.legalId') ?: $normalizedLegalId));

        if ($code !== '00' || $resolvedName === '') {
            return $this->buildResult(
                success: false,
                status: 'not_found',
                message: $desc !== '' ? $desc : 'Không tìm thấy thông tin phù hợp để đối chiếu.',
                data: [
                    'normalizedLegalId' => $normalizedLegalId,
                    'normalizedLegalName' => $normalizedLegalName,
                ]
            );
        }

        $matched = $this->normalizeNameForCompare($resolvedName) === $this->normalizeNameForCompare($fullName);

        return $this->buildResult(
            success: $matched,
            status: $matched ? 'matched' : 'mismatched',
            message: $matched
                ? 'Thông tin tra cứu khớp với dữ liệu đã nhập.'
                : 'Tên tra cứu không khớp hoàn toàn với dữ liệu đã nhập.',
            data: [
                'legalId' => $resolvedLegalId,
                'name' => $resolvedName,
                'normalizedLegalId' => $normalizedLegalId,
                'normalizedLegalName' => $normalizedLegalName,
            ]
        );
    }

    public function normalizeLegalId(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?? '';
    }

    public function normalizeLegalName(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return Str::upper(Str::ascii($value));
    }

    private function normalizeNameForCompare(string $value): string
    {
        return Str::upper(Str::ascii($this->normalizeLegalName($value)));
    }

    private function buildResult(bool $success, string $status, string $message, array $data = []): array
    {
        return [
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }
}
