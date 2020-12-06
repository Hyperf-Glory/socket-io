<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */

use App\Controller\SocketIOController;
use App\Controller\WebSocketController;
/*
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Milddleware\HttpAuthMiddleware;
use Hyperf\HttpServer\Router\Router;
use Hyperf\SocketIOServer\Collector\SocketIORouter;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

/* ---------------------- HTTP-Auth -------------------------- */
Router::addGroup('/api/auth/', function () {
    // ------- 鉴权 ----------//
    Router::post('register', 'App\Controller\AuthController@register');
    Router::post('login', 'App\Controller\AuthController@login');
    Router::post('send-verify-code', 'App\Controller\AuthController@sendVerifyCode');
});
/* ----------------------  结束   ------------------------------------ */
/* ---------------------- HTTP-User -------------------------- */
Router::addGroup('/api/user/', function () {
    Router::get('setting', 'App\Controller\UserController@getUserSetting');
    Router::get('friend-apply-num', 'App\Controller\UserController@getApplyUnreadNum');
    Router::get('friends', 'App\Controller\UserController@getUserFriends');
    Router::post('remove-friend', 'App\Controller\UserController@removeFriend');
    Router::get('user-groups', 'App\Controller\UserController@getUserGroups');
    Router::get('detail', 'App\Controller\UserController@editAvatar');
    Router::post('edit-user-detail', 'App\Controller\UserController@editUserDetail');
    Router::post('edit-avatar', 'App\Controller\UserController@editAvatar');
    Router::post('search-user', 'App\Controller\UserController@searchUserInfo');
    Router::post('edit-friend-remark', 'App\Controller\UserController@editFriendRemark');
    Router::post('send-friend-apply', 'App\Controller\UserController@sendFriendApply');
    Router::post('handle-friend-apply', 'App\Controller\UserController@handleFriendApply');
    Router::post('delete-friend-apply', 'App\Controller\UserController@deleteFriendApply');
    Router::get('friend-apply-records', 'App\Controller\UserController@getFriendApplyRecords');
    Router::post('change-password', 'App\Controller\UserController@editUserPassword');
    Router::post('change-mobile', 'App\Controller\UserController@editUserMobile');
    Router::post('change-email', 'App\Controller\UserController@editUserEmail');
    Router::post('send-mobile-code', 'App\Controller\UserController@sendMobileCode');
    Router::post('send-change-email-code', 'App\Controller\UserController@sendChangeEmailCode');
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */
/* ---------------------- HTTP-Talk -------------------------- */
Router::addGroup('/api/talk/', function () {
    Router::get('list', 'App\Controller\TalkController@list');
    Router::post('create', 'App\Controller\TalkController@create');
    Router::post('delete', 'App\Controller\TalkController@delete');
    Router::post('topping', 'App\Controller\TalkController@topping');
    Router::post('set-not-disturb', 'App\Controller\TalkController@setNotDisturb');
    Router::post('update-unread-num', 'App\Controller\TalkController@updateUnreadNum');

    Router::post('revoke-records', 'App\Controller\TalkController@revokeChatRecords');
    Router::post('remove-records', 'App\Controller\TalkController@removeChatRecords');
    Router::post('forward-records', 'App\Controller\TalkController@forwardChatRecords');

    Router::get('records', 'App\Controller\TalkController@getChatRecords');
    Router::get('get-forward-records', 'App\Controller\TalkController@getForwardRecords');
    Router::get('find-chat-records', 'App\Controller\TalkController@findChatRecords');
    Router::get('search-chat-records', 'App\Controller\TalkController@searchChatRecords');
    Router::get('get-records-context', 'App\Controller\TalkController@getRecordsContext');

    Router::post('send-image', 'App\Controller\TalkController@sendImage');
    Router::post('send-code-block', 'App\Controller\TalkController@sendCodeBlock');
    Router::post('send-file', 'App\Controller\TalkController@sendFile');
    Router::post('send-emoticon', 'App\Controller\TalkController@sendEmoticon');
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */

/* --------------------- HTTP-Group -------------------------- */
Router::addGroup('/api/group/', function () {
    Router::post('create', 'App\Controller\GroupController@create');
    Router::post('edit', 'App\Controller\GroupController@editDetail');
    Router::post('invite', 'App\Controller\GroupController@invite');
    Router::post('dismiss', 'App\Controller\GroupController@dismiss');
    Router::post('secede', 'App\Controller\GroupController@secede');

    Router::post('set-group-card', 'App\Controller\GroupController@setGroupCard');
    Router::post('edit-notice', 'App\Controller\GroupController@editNotice');
    Router::post('delete-notice', 'App\Controller\GroupController@deleteNotice');
    Router::post('remove-members', 'App\Controller\GroupController@removeMembers');

    Router::get('detail', 'App\Controller\GroupController@detail');
    Router::get('invite-friends', 'App\Controller\GroupController@getInviteFriends');
    Router::get('members', 'App\Controller\GroupController@members');
    Router::get('notices', 'App\Controller\GroupController@getGroupNotices');
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */
/* --------------------- HTTP-File -------------------------- */
Router::addGroup('/api/upload/', function () {
    Router::post('file-stream', 'App\Controller\UploadController@fileStream');
    Router::post('file-subarea-upload', 'App\Controller\UploadController@fileSubareaUpload');
    Router::get('get-file-split-info', 'App\Controller\UploadController@getFileSplitInfo');
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */
/* --------------------- HTTP-Emoticon -------------------------- */
Router::addGroup('/api/emoticon/', function () {
    Router::get('user-emoticon', 'App\Controller\EmoticonController@getUserEmoticon');
    Router::get('system-emoticon', 'App\Controller\EmoticonController@getSystemEmoticon');
    Router::post('set-user-emoticon', 'App\Controller\EmoticonController@setUserEmoticon');
    Router::post('upload-emoticon', 'App\Controller\EmoticonController@uploadEmoticon');
    Router::post('collect-emoticon', 'App\Controller\EmoticonController@collectEmoticon');
    Router::post('del-collect-emoticon', 'App\Controller\EmoticonController@delCollectEmoticon');
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */
/* --------------------- HTTP-Download -------------------------- */
Router::addGroup('/api/download/', static function () {
}, [
    'middleware' => [HttpAuthMiddleware::class],
]);
/* ----------------------  结束   ------------------------------------ */
SocketIORouter::addNamespace('/', SocketIOController::class);
Router::get('/favicon.ico', function () {
    return '';
});
