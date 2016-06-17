<?php

return [
    [
        'name' => 'media.admin.libraries.create.get',
        'description' => 'inoplate-media::permissions.media_admin_library_create_get',
        'aliases' => [
            'media.admin.libraries.upload.post'
        ]
    ],
    [
        'name' => 'media.admin.libraries.update.get',
        'description' => 'inoplate-media::permissions.media_admin_library_update_get',
        'interceptors' => ['media.admin.libraries.update.all'],
        'aliases' => [
            'media.admin.libraries.update.put'
        ]
    ],
    [
        'name' => 'media.admin.libraries.share.get',
        'description' => 'inoplate-media::permissions.media_admin_library_share_get',
        'interceptors' => ['media.admin.libraries.share.all'],
        'aliases' => [
            'media.admin.libraries.share.put'
        ]
    ],
    [
        'name' => 'media.admin.libraries.manage-publishment.get',
        'description' => 'inoplate-media::permissions.media_admin_library_manage_publishment_get',
        'interceptors' => ['media.admin.libraries.manage-publishment.all'],
        'aliases' => [
            'media.admin.libraries.publish.put',
            'media.admin.libraries.unpublish.put',
        ]
    ],
    [
        'name' => 'media.admin.libraries.delete.get',
        'description' => 'inoplate-media::permissions.media_admin_library_delete_get',
        'interceptors' => ['media.admin.libraries.delete.all'],
        'aliases' => [
            'media.admin.libraries.delete'
        ]
    ],
    [
        'name' => 'media.admin.libraries.view.all',
        'description' => 'inoplate-media::permissions.media_admin_library_view_all',
        'aliases' => []
    ],
    [
        'name' => 'media.admin.libraries.share.all',
        'description' => 'inoplate-media::permissions.media_admin_library_share_all',
        'aliases' => []
    ],
    [
        'name' => 'media.admin.libraries.manage-publishment.all',
        'description' => 'inoplate-media::permissions.media_admin_library_manage_publishment_all',
        'aliases' => []
    ],
    [
        'name' => 'media.admin.libraries.update.all',
        'description' => 'inoplate-media::permissions.media_admin_library_update_all',
        'aliases' => []
    ],
    [
        'name' => 'media.admin.libraries.delete.all',
        'description' => 'inoplate-media::permissions.media_admin_library_delete_all',
        'aliases' => []
    ],
];