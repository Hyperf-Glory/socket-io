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
use App\Milddleware\HttpAuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

/** ---------------------- HTTP-Auth -------------------------- */
Router::addGroup('/api/auth/', function ()
{
    // ------- 鉴权 ----------//
    Router::post('register', 'App\Controller\AuthController@register');
    Router::post('login', 'App\Controller\AuthController@login');
    Router::post('send-verify-code', 'App\Controller\AuthController@sendVerifyCode');
});
/** ----------------------  结束   ------------------------------------ */
/** ---------------------- HTTP-User -------------------------- */
Router::addGroup('/api/user/', function ()
{
    Router::get('setting', 'App\Controller\UserController@setting');
    Router::get('friend-apply-num', 'App\Controller\UserController@friendApplyNum');
}, [
    'middleware' => [HttpAuthMiddleware::class]
]);
/** ----------------------  结束   ------------------------------------ */
/** ---------------------- HTTP-Talk -------------------------- */
Router::addGroup('/api/talk/', function ()
{
    Router::get('list', 'App\Controller\TalkController@list');
}, [
    'middleware' => [HttpAuthMiddleware::class]
]);
/** ----------------------  结束   ------------------------------------ */

/** --------------------- HTTP-Group -------------------------- */
Router::addGroup('/api/group/', function ()
{
    Router::post('create', 'App\Controller\GroupController@create');
    Router::post('detail', 'App\Controller\GroupController@detail');
    Router::post('editDetail', 'App\Controller\GroupController@editDetail');
}, [
    'middleware' => [HttpAuthMiddleware::class]
]);
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
