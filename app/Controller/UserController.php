<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Cache\ApplyNumCache;
use App\Service\UserService;

class UserController extends AbstractController
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     *
     */
    public function setting()
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
     * //TODO 11.13日需要完成的
     */
    public function friendApplyNum()
    {
        $user = $this->request->getAttribute('user');
        return $this->response->success('success', [
            'unread_num' => ApplyNumCache::get($user['id'])
        ]);
    }
}
