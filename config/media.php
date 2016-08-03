<?php

return [
    'notifier' => ['database'],
    'library' => [
        'per_page' => 8,
        'chunks_temp_path' => 'chunks', // Path to store chunks
        'size' => [
            'max' => '8', // in MB
            'chunk' => '1' // in MB
        ],
        'extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', // Image file extensions
            'pdf', 'doc', 'docx', 'ppt', 'pptx', 'pps', 'ppsx', 'odt', 'ods', 'xls', 'xlsx', 'psd', // Documents extensions
            'mp3', 'm4a', 'ogg', 'wav', 'mpga', // Audio extensions
            'mp4', 'm4v', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gp', '3g2', // Video extensions
            'zip', 'gzip', 'tar.gz', 'rar' // Compressed files extension
        ],
        'sizes' => [
            'large' => [
                'dimension' => 'large',
                'width' => '480',
                'height' => '360'
            ],
            'thumb' => [
                'dimension' => 'large',
                'width' => '150',
                'height' => '150'
            ],
            'medium' => [
                'dimension' => 'medium',
                'width' => '240',
                'height' => '180'
            ],
            'small' => [
                'dimension' => 'small',
                'width' => '120',
                'height' => '90'
            ],
            'full-display' => [
                'dimension' => 'small',
                'width' => '800',
                'height' => '600',
                'mode' => 'heighten'
            ],
            'mini-display' => [
                'dimension' => 'mini-display',
                'width' => null,
                'height' => '225',
                'mode' => 'heighten'
            ],
        ],
        'resize_mode' => 'fit',
        'cache_lifetime' => 86400, // 1 day
        'default_visibility' => 'private', // enum['private', 'public']
        'cache_lifetime' => 150 // Cache lifetime
    ],
];