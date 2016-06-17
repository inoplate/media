<?php

$router->get('uploads/{path}/{template?}', ['uses' => 'DownloadController@getRender', 'as' => 'media.admin.libraries.render.get'])
           ->where('path', '(.*\.[^/]*)$');
$router->get('download/{path}', ['uses' => 'DownloadController@getDownload', 'as' => 'media.admin.libraries.download.get'])
       ->where('path', '(.*\.[^/]*)$');

// Protected routes
// Only authenticated and authorized user can access this endpoints

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function($router){
    $router->model('library', 'Inoplate\Media\MediaLibrary');
    $router->get('inoplate-media/libraries/shareable-users/{library}', ['uses' => 'LibraryController@getShareableUsers', 'as' => 'media.admin.libraries.shareable-users.get']);

    $router->get('inoplate-media/libraries', ['uses' => 'LibraryController@getIndex', 'as' => 'media.admin.libraries.index.get']);
    $router->group(['middleware' => ['authorize:library']], function($router) {
        $router->get('inoplate-media/libraries/upload', ['uses' => 'LibraryController@upload', 'as' => 'media.admin.libraries.upload.get']);
        $router->post('inoplate-media/libraries/upload', ['uses' => 'LibraryController@upload', 'as' => 'media.admin.libraries.upload.post']);

        $router->get('inoplate-media/libraries/{library}/edit', [
            'uses' => 'LibraryController@putUpdate', 
            'as' => 'media.admin.libraries.update.get'
        ]);

        $router->put('inoplate-media/libraries/{library}', ['uses' => 'LibraryController@putUpdate', 'as' => 'media.admin.libraries.update.put']);

        $router->put('inoplate-media/libraries/publish/{library}', ['uses' => 'LibraryController@putPublish', 'as' => 'media.admin.libraries.publish.put']);
        $router->put('inoplate-media/libraries/unpublish/{library}', ['uses' => 'LibraryController@putUnpublish', 'as' => 'media.admin.libraries.unpublish.put']);

        $router->put('inoplate-media/libraries/share/{library}', ['uses' => 'LibraryController@putShare', 'as' => 'media.admin.libraries.share.put']);
        $router->delete('inoplate-media/libraries/{library}', ['uses' => 'LibraryController@delete', 'as' => 'media.admin.libraries.delete']);
    });
});