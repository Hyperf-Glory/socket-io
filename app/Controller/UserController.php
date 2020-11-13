<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Cache\ApplyNumCache;
use App\Helper\ValidateHelper;
use App\Kernel\SocketIO;
use App\Model\UsersFriends;
use App\Service\UserService;
use Hyperf\Redis\RedisFactory;

class UserController extends AbstractController
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * 用户相关设置
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserSetting()
    {
        $user = $this->request->getAttribute('user');
        $info = $this->service->findById($user['id'], ['id', 'nickname', 'avatar', 'motto', 'gender']);
        return $this->response->success('success', [
            'user_info' => [
                'uid'      => $info->id,
                'nickname' => $info->nickname,
                'avatar'   => $info->avatar,
                'motto'    => $info->motto,
                'gender'   => $info->gender,
            ],
            'setting'   => [
                'theme_mode'            => '',
                'theme_bag_img'         => '',
                'theme_color'           => '',
                'notify_cue_tone'       => '',
                'keyboard_event_notify' => '',
            ]
        ]);
    }

    /**
     * 获取好友申请未读数
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getApplyUnreadNum()
    {
        $user = $this->request->getAttribute('user');
        return $this->response->success('success', [
            'unread_num' => ApplyNumCache::get($user['id'])
        ]);
    }

    /**
     * 获取我的信息
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserDetail()
    {
        $user     = $this->request->getAttribute('user');
        $userInfo = $this->service->findById($user['id'], ['mobile', 'nickname', 'avatar', 'motto', 'email', 'gender']);
        return $this->response->success('success', [
            'mobile'   => $userInfo->mobile,
            'nickname' => $userInfo->nickname,
            'avatar'   => $userInfo->avatar,
            'motto'    => $userInfo->motto,
            'email'    => $userInfo->email,
            'gender'   => $userInfo->gender
        ]);
    }

    /**
     * 获取我的好友列表
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserFriends()
    {
        $user  = $this->request->getAttribute('user');
        $rows  = UsersFriends::getUserFriends($user['id']);
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $cache = array_keys($redis->hGetAll(SocketIO::HASH_UID_TO_FD_PREFIX));

        foreach ($rows as $k => $row) {
            $rows[$k]['online'] = in_array($row['id'], $cache) ? true : false;
        }
        return $this->response->success('success', $rows);
    }

    public function editUserDetail()
    {
        $params = ['nickname', 'avatar', 'motto', 'gender'];
        if(!$this->request->has($params)||ValidateHelper::isInteger($this->request->post('gender'))){
            return $this->response->fail(301,'参数错误!');
        }
        //TODO 编辑个人资料
    }
}
