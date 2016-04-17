<?php

// Protected routes
// Only authenticated and authorized user can access this endpoints

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function($router){
    $router->get('inoplate-media/libraries', ['uses' => 'LibraryController@getIndex', 'as' => 'media.admin.libraries.index.get']);
    $router->get('inoplate-media/libraries/upload', ['uses' => 'LibraryController@upload', 'as' => 'media.admin.libraries.upload.get']);
    $router->post('inoplate-media/libraries/upload', ['uses' => 'LibraryController@upload', 'as' => 'media.admin.libraries.upload.post']);
    $router->get('inoplate-media/libraries/{id}', ['uses' => 'LibraryController@getUpdate', 'as' => 'media.admin.libraries.update.get']);
});