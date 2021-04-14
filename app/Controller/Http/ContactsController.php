<?php
declare(strict_types = 1);

namespace App\Controller\Http;

use App\Cache\ApplyNumCache;
use App\Cache\FriendRemarkCache;
use App\Controller\AbstractController;
use App\Model\UsersChatList;
use App\Model\UsersFriend;
use App\Service\UserFriendService;
use App\Service\UserService;
use App\SocketIO\SocketIO;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

class ContactsController extends AbstractController
{
    private UserService $service;

    private UserFriendService $friendService;

    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(UserService $service, UserFriendService $friendService, ValidatorFactoryInterface $validationFactory)
    {
        $this->service           = $service;
        $this->friendService     = $friendService;
        $this->validationFactory = $validationFactory;
    }

    /**
     * 获取用户联系人列表
     */
    public function getContacts() : ResponseInterface
    {
        $rows  = UsersFriend::getUserFriends($this->uid());
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $cache = array_keys($redis->hGetAll(SocketIO::HASH_UID_TO_SID_PREFIX));

        foreach ($rows as $k => $row) {
            $rows[$k]['online'] = in_array($row['id'], $cache, true);
        }
        return $this->response->success('success', $rows);
    }

    /**
     *添加联系人
     */
    public function addContact(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'friend_id' => 'required',
                'remarks'   => 'required',
            ],
            [
                'friend_id.required' => '好友不能为空...',
                'remarks.required'   => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->addFriendApply($this->uid(), (int)$data['friend_id'], $data['remarks']);
        if (!$bool) {
            return $this->response->error('发送好友申请失败...');
        }
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));

        //断对方是否在线。如果在线发送消息通知
        if ($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string)$data['friend_id'])) {

        }
        // 好友申请未读消息数自增
        make(ApplyNumCache::class)->setInc((int)$data['friend_id']);
        return $this->response->success('发送好友申请成功...');
    }

    /**
     * 删除联系人
     */
    public function deleteContact(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'friend_id' => 'required|integer',
            ],
            [
                'friend_id.required' => '参数不能为空...',
                'friend_id.integer'  => '参数不正确...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        if (!$this->friendService->removeFriend($this->uid(), (int)$data['friend_id'])) {
            return $this->response->error('解除好友失败...');
        }
        //删除好友会话列表
        UsersChatList::delItem($this->uid(), $data['friend_id'], 2);
        UsersChatList::delItem($data['friend_id'], $this->uid(), 2);
        //TODO ... 推送消息（待完善）
        return $this->response->success('success');
    }

    /**
     * 同意添加联系人
     */
    public function acceptInvitation(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'apply_id' => 'required',
                'remarks'  => 'required',
            ],
            [
                'apply_id.required' => '申请不能为空...',
                'remarks.required'  => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->handleFriendApply($this->uid(), (int)$data['apply_id'], $data['remarks']);
        //判断是否是同意添加好友
        if ($bool) {
            //... 推送处理消息
        }
        return $bool ? $this->response->success('处理完成...') : $this->response->error('处理失败，请稍后再试...');
    }

    /**
     *  拒绝添加联系人(预留)
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function declineInvitation(RequestInterface $request) : ResponseInterface
    {
        $params = $this->request->inputs(['apply_id', 'remarks']);
        $this->validationFactory->make($params, [
            'apply_id' => 'required|integer',
            'remarks'  => 'present|max:20'
        ]);

        $isTrue = $this->friendService->declineInvitation($this->uid(), (int)$params['apply_id'], $params['remarks']);

        return $isTrue
            ? $this->response->success()
            : $this->response->fail();
    }

    /**
     * 删除联系人申请记录
     */
    public function deleteContactApply() : ResponseInterface
    {
        $params    = $this->request->inputs(['apply_id']);
        $validator = $this->validationFactory->make($params, [
            'apply_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $isTrue = $this->friendService->delFriendApply($this->uid(), (int)$params['apply_id']);

        return $isTrue
            ? $this->response->success()
            : $this->response->fail();
    }

    /**
     * 获取联系人申请未读数
     */
    public function getContactApplyRecords(RequestInterface $request) : ResponseInterface
    {
        $page     = (int)$request->input('page', 1);
        $pageSize = (int)$request->input('page_size', 10);
        $data     = $this->friendService->findApplyRecords($this->uid(), $page, $pageSize);
        ApplyNumCache::del($this->uid());
        return $this->response->success('success', $data);
    }

    /**
     * 获取联系人申请未读数
     */
    public function getContactApplyUnreadNum() : ResponseInterface
    {
        return $this->response->success('success', [
            'unread_num' => (int)ApplyNumCache::get($this->uid()),
        ]);
    }

    /**
     * 搜索联系人
     */
    public function searchContacts(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'mobile' => 'required|mobile',
            ],
            [
                'mobile.required' => '手机号不能为空...',
                'mobile.mobile'   => '手机号格式不正确...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        if ($data = $this->service->findContact($data['mobile'])) {
            return $this->response->success('success', $data);
        }
        return $this->response->error('success');
    }

    /**
     * 编辑好友备注信息.
     */
    public function editContactRemark(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'apply_id' => 'required',
                'remarks'  => 'required',
            ],
            [
                'apply_id.required' => '申请不能为空...',
                'remarks.required'  => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->editFriendRemark($this->uid(), (int)$data['friend_id'], $data['remarks']);
        if ($bool) {
            FriendRemarkCache::set($this->uid(), (int)$data['friend_id'], $data['remarks']);
        }
        return $bool ? $this->response->success('备注修改成功...') : $this->response->error('备注修改失败，请稍后再试...');
    }
}
