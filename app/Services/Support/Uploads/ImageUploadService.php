<?php

namespace App\Services\Support\Uploads;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    public function validateAndStore(Request $request, string $preset, string $field = 'file'): array
    {
        $request->validate(
            $this->rules($preset, $field),
            $this->messages($field)
        );

        $file = $request->file($field);

        if (!$file instanceof UploadedFile) {
            throw new InvalidArgumentException("Upload field [{$field}] is invalid.");
        }

        return $this->store($file, $preset);
    }

    public function rules(string $preset, string $field = 'file'): array
    {
        $config = $this->preset($preset);

        $rules = [
            $field => [
                'required',
                'image',
                'mimes:' . implode(',', $config['allowed_extensions']),
                'max:' . (int) $config['max_kb'],
            ],
        ];

        if (isset($config['max_width'], $config['max_height'])) {
            $rules[$field][] = 'dimensions:max_width=' . (int) $config['max_width']
                . ',max_height=' . (int) $config['max_height'];
        }

        return $rules;
    }

    public function store(UploadedFile $file, string $preset): array
    {
        $config = $this->preset($preset);
        $disk = (string) ($config['disk'] ?? 'public');
        $directory = trim((string) ($config['directory'] ?? 'uploads/images'), '/');

        if (($config['transform']['encode'] ?? null) === 'jpeg') {
            return $this->storeAsJpeg($file, $preset, $disk, $directory, $config);
        }

        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        $filename = (string) Str::uuid() . '.' . $extension;
        $path = $file->storeAs($directory, $filename, $disk);

        [$width, $height] = $this->dimensionsFor($file);

        return [
            'preset' => $preset,
            'disk' => $disk,
            'path' => $path,
            'url' => $this->urlFor($disk, $path),
            'filename' => $filename,
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => (string) ($file->getMimeType() ?: ''),
            'extension' => $extension,
            'size' => (int) ($file->getSize() ?: Storage::disk($disk)->size($path)),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function presetNames(): array
    {
        return array_keys(config('uploads.image_presets', []));
    }

    private function storeAsJpeg(
        UploadedFile $file,
        string $preset,
        string $disk,
        string $directory,
        array $config
    ): array {
        $manager = new ImageManager(new Driver());
        $image = $manager->decode($file->getRealPath());
        $image->scaleDown(
            width: (int) ($config['transform']['max_width'] ?? 400),
            height: (int) ($config['transform']['max_height'] ?? 400)
        );

        $encoded = $image->encode(new JpegEncoder(
            quality: (int) ($config['transform']['quality'] ?? 85)
        ));

        $filename = (string) Str::uuid() . '.jpg';
        $path = $directory . '/' . $filename;
        $binary = (string) $encoded;

        Storage::disk($disk)->put($path, $binary);

        return [
            'preset' => $preset,
            'disk' => $disk,
            'path' => $path,
            'url' => $this->urlFor($disk, $path),
            'filename' => $filename,
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => strlen($binary),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    private function preset(string $preset): array
    {
        $config = config("uploads.image_presets.{$preset}");

        if (!is_array($config)) {
            throw new InvalidArgumentException("Unknown upload preset [{$preset}].");
        }

        return $config;
    }

    private function messages(string $field): array
    {
        return [
            "{$field}.required" => 'Vui lòng chọn ảnh.',
            "{$field}.image" => 'File phải là ảnh.',
            "{$field}.mimes" => 'Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.',
            "{$field}.max" => 'Ảnh vượt quá dung lượng cho phép.',
            "{$field}.dimensions" => 'Kích thước ảnh vượt quá giới hạn cho phép.',
        ];
    }

    private function urlFor(string $disk, string $path): string
    {
        $url = Storage::disk($disk)->url($path);

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return asset(ltrim($url, '/'));
    }

    private function dimensionsFor(UploadedFile $file): array
    {
        $dimensions = @getimagesize($file->getRealPath());

        if (!is_array($dimensions)) {
            return [null, null];
        }

        return [
            isset($dimensions[0]) ? (int) $dimensions[0] : null,
            isset($dimensions[1]) ? (int) $dimensions[1] : null,
        ];
    }
}
