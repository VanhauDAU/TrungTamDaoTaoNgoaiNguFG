<?php

return [
    'image_presets' => [
        'avatar' => [
            'disk' => 'public',
            'directory' => 'anh-dai-dien',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_kb' => 2048,
            'max_width' => 5000,
            'max_height' => 5000,
            'transform' => [
                'encode' => 'jpeg',
                'quality' => 85,
                'max_width' => 400,
                'max_height' => 400,
            ],
        ],
        'content_image' => [
            'disk' => 'public',
            'directory' => 'bai-viet/content',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_kb' => 4096,
            'max_width' => 8000,
            'max_height' => 8000,
        ],
    ],
];
