<?php

declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Milddleware\AuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

/** ---------------------- HTTP鉴权 -------------------------- */
Router::addGroup('/api/auth/', function ()
{
    // ------- 鉴权 ----------//
    Router::post('register', 'App\Controller\AuthController@register');
    Router::post('login', 'App\Controller\AuthController@login');
    Router::post('send-verify-code', 'App\Controller\AuthController@sendVerifyCode');
});

/** ----------------------  结束   ------------------------------------ */
Router::addServer('ws', function ()
{
    Router::get('/', 'App\Controller\WebSocketController', [
        'middleware' => [AuthMiddleware::class],
    ]);
});
Router::get('/favicon.ico', function ()
{
    return '';
});
