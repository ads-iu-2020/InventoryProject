<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function() use ($router) {
    $router->group(['prefix' => 'products'], function() use ($router) {
        $router->get('list', 'ProductController@list');
        $router->get('search', 'ProductController@search');
    });

    $router->group(['prefix' => 'stocks'], function() use ($router) {
        $router->get('list', 'StockController@list');
        $router->get('reserve', 'StockController@reserve');
        $router->get('cancel', 'StockController@cancel');
        $router->get('history', 'StockController@history');
        $router->get('history/day', 'StockController@dayHistory');
        $router->get('changes', 'StockController@changes');
    });
});
