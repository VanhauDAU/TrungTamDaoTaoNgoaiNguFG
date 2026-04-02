@props([
    'id' => null,
    'action' => null,
    'method' => 'POST',
    'name' => 'file',
    'title' => 'Tải ảnh',
    'description' => null,
    'chooseLabel' => 'Chọn ảnh',
    'confirmLabel' => 'Xác nhận',
    'cancelLabel' => 'Hủy',
    'pendingLabel' => 'Ảnh sẽ được lưu khi bạn gửi biểu mẫu.',
    'previewUrl' => '',
    'previewAlt' => 'Ảnh xem trước',
    'previewShape' => 'rectangle',
    'accept' => 'image/*',
    'allowedTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowedExtensionsLabel' => 'JPG, PNG, GIF, WebP',
    'maxSize' => 2097152,
    'maxSizeLabel' => '2MB',
    'hint' => null,
    'dropLabel' => 'Hỗ trợ kéo và thả trực tiếp vào vùng xem trước',
    'syncSelector' => '',
    'responseUrlKey' => '',
    'errorBagKey' => null,
    'mode' => 'instant',
    'standalone' => true,
])

@php
    use Illuminate\Support\Str;

    $componentId = $id ?: 'upload-' . Str::lower((string) Str::ulid());
    $inputId = $componentId . '-input';
    $resolvedErrorKey = $errorBagKey ?: $name;
    $resolvedPreviewUrl = filled($previewUrl)
        ? $previewUrl
        : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
@endphp

<div {{ $attributes->class('ui-upload') }}
    data-upload-component
    data-allowed-types="{{ implode(',', $allowedTypes) }}"
    data-allowed-label="{{ $allowedExtensionsLabel }}"
    data-max-size="{{ $maxSize }}"
    data-max-size-label="{{ $maxSizeLabel }}"
    data-sync-selector="{{ $syncSelector }}"
    data-response-url-key="{{ $responseUrlKey }}"
    data-preview-shape="{{ $previewShape }}"
    data-upload-mode="{{ $mode }}">
    @if ($standalone)
        <form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
            enctype="multipart/form-data" data-upload-form>
            @if (strtoupper($method) !== 'GET')
                @csrf
            @endif
            @if (!in_array(strtoupper($method), ['GET', 'POST'], true))
                @method($method)
            @endif
    @endif

    {{ $slot }}

    <div class="ui-upload__layout">
        <div class="ui-upload__card">
            <button type="button" class="ui-upload__dropzone" data-upload-dropzone aria-label="{{ $chooseLabel }}">
                <span class="ui-upload__preview-shell">
                    <img src="{{ $resolvedPreviewUrl }}" alt="{{ $previewAlt }}" data-upload-preview>
                    <span class="ui-upload__overlay">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Thả ảnh hoặc nhấn để chọn</span>
                    </span>
                </span>
            </button>

            @if ($mode === 'instant')
                <div class="ui-upload__quick-actions d-none" data-upload-actions>
                    <button type="button" class="btn ui-upload__confirm" data-upload-confirm>
                        <i class="fas fa-upload"></i>
                        <span>{{ $confirmLabel }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary ui-upload__cancel" data-upload-cancel>
                        <i class="fas fa-times"></i>
                        <span>{{ $cancelLabel }}</span>
                    </button>
                </div>

                <div class="ui-upload__progress d-none" data-upload-progress-wrap>
                    <div class="ui-upload__progress-track">
                        <div class="ui-upload__progress-fill" data-upload-progress-fill style="width: 0%"></div>
                    </div>
                    <div class="ui-upload__progress-footer">
                        <span data-upload-progress-text>Đang chuẩn bị...</span>
                        <span data-upload-progress-pct>0%</span>
                    </div>
                </div>
            @else
                <div class="ui-upload__quick-actions d-none" data-upload-actions>
                    <button type="button" class="btn btn-outline-secondary ui-upload__cancel" data-upload-cancel>
                        <i class="fas fa-times"></i>
                        <span>{{ $cancelLabel }}</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="ui-upload__content">
            <div class="ui-upload__header">
                <strong>{{ $title }}</strong>
                @if ($description)
                    <p>{{ $description }}</p>
                @endif
            </div>

            <div class="ui-upload__toolbar">
                <label class="btn btn-outline-secondary btn-sm" for="{{ $inputId }}">
                    <i class="fas fa-folder-open me-1"></i>{{ $chooseLabel }}
                </label>
                <span class="ui-upload__drop-hint">
                    <i class="fas fa-arrows-up-down-left-right"></i>
                    <span>{{ $dropLabel }}</span>
                </span>
            </div>

            <input type="file" id="{{ $inputId }}" name="{{ $name }}" accept="{{ $accept }}" class="d-none"
                data-upload-input>

            @if ($hint)
                <p class="ui-upload__guideline">{{ $hint }}</p>
            @endif

            <div class="ui-upload__selected-file d-none" data-upload-selected></div>
            <div class="ui-upload__feedback d-none" data-upload-feedback></div>

            @if ($mode === 'instant')
                <noscript>
                    <button type="submit" class="btn ui-upload__confirm ui-upload__noscript-submit">
                        <i class="fas fa-upload me-1"></i>Tải lên
                    </button>
                </noscript>
            @else
                <p class="ui-upload__deferred-note">
                    <i class="fas fa-floppy-disk"></i>
                    <span>{{ $pendingLabel }}</span>
                </p>
            @endif

            @error($resolvedErrorKey)
                <div class="text-danger small mt-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>
    </div>

    @if ($standalone)
        </form>
    @endif
</div>
