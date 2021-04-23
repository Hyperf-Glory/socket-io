<?php
declare(strict_types = 1);

namespace App\Controller\Http;

use App\Cache\LastMsgCache;
use App\Component\UnreadTalk;
use App\Controller\AbstractController;
use App\Helper\ArrayHelper;
use App\Model\Group;
use App\Model\User;
use App\Model\UsersChatList;
use App\Model\UsersFriend;
use App\Service\TalkService;
use App\SocketIO\Proxy\ForwardChatRecords;
use App\SocketIO\Proxy\RevokeRecord;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Coroutine;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

class TalkController extends AbstractController
{
    protected TalkService $service;

    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(TalkService $service, ValidatorFactoryInterface $validationFactory)
    {
        $this->service           = $service;
        $this->validationFactory = $validationFactory;
    }

    /**
     * 获取用户对话列表.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function list() : ResponseInterface
    {
        $list = $this->container->get(UnreadTalk::class)->getAll($this->uid());
        if ($list) {
            $this->service->updateUnreadTalkList($this->uid(), $list);
        }
        $rows = $this->service->talks($this->uid());
        if ($rows) {
            $rows = ArrayHelper::sortByField($rows, 'updated_at');
        }
        return $this->response->success('success', $rows);
    }

    /**
     *
     * 新增对话列表
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'type'       => 'required|in:1,2',
            'receive_id' => 'present|integer|min:0'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        if ($data['type'] === 1) {
            if (!UsersFriend::isFriend($this->uid(), $data['receive_id'])) {
                return $this->response->fail(305, '暂不属于好友关系，无法进行聊天...');
            }
        } elseif (!Group::isMember($data['receive_id'], $this->uid())) {
            return $this->response->fail(305, '暂不属于群成员，无法进行群聊 ...');
        }

        $result = UsersChatList::addItem($this->uid(), $data['receive_id'], $data['type']);
        if (!$result) {
            return $this->response->error('创建失败...');
        }

        $data = [
            'id'          => $result['id'],
            'type'        => $result['type'],
            'group_id'    => $result['group_id'],
            'friend_id'   => $result['friend_id'],
            'is_top'      => 0,
            'msg_text'    => '',
            'not_disturb' => 0,
            'online'      => 1,
            'name'        => '',
            'remark_name' => '',
            'avatar'      => '',
            'unread_num'  => 0,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($result['type'] === 1) {
            $data['unread_num'] = $this->container->get(UnreadTalk::class)->get($this->uid(), $result['friend_id']);

            /**
             * @var User $userInfo
             */
            $userInfo       = User::where('id', $this->uid())->first(['nickname', 'avatar']);
            $data['name']   = $userInfo->nickname;
            $data['avatar'] = $userInfo->avatar;
        } elseif ($result['type'] === 2) {
            /**
             * @var Group $groupInfo
             */
            $groupInfo      = Group::where('id', $result['group_id'])->first(['group_name', 'avatar']);
            $data['name']   = $groupInfo->group_name;
            $data['avatar'] = $groupInfo->avatar;
        }

        $records = $this->container->get(LastMsgCache::class)->get($result['type'] === 1 ? (int)$result['friend_id'] : (int)$result['group_id'], $result['type'] === 1 ? $this->uid() : 0);
        if ($records) {
            $data['msg_text']   = $records['text'];
            $data['updated_at'] = $records['created_at'];
        }
        return $this->response->success('创建成功...', ['talkItem' => $data]);
    }

    /**
     * 删除对话列表.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete() : ResponseInterface
    {
        $list_id = (int)$this->request->post('list_id', 0);
        $bool    = UsersChatList::delItem($this->uid(), $list_id);
        return $bool ? $this->response->success('操作完成...') : $this->response->error('操作失败...');
    }

    /**
     * 对话列表置顶.
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function topping(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'list_id' => 'required|integer|min:0',
            'type'    => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = UsersChatList::topItem($this->uid(), $data['list_id'], $data['type'] === 1);
        return $bool ? $this->response->success('操作完成...') : $this->response->error('操作失败...');
    }

    /**
     * 设置消息免打扰状态
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setNotDisturb(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'receive_id'  => 'required|integer|min:0',
            'type'        => 'required|in:1,2',
            'not_disturb' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = UsersChatList::notDisturbItem($this->uid(), $data['receive_id'], $data['type'], $data['not_disturb']);

        return $bool ? $this->response->success('设置成功...') : $this->response->error('设置失败...');
    }

    /**
     * 更新对话列表未读数
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateUnreadNum(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'receive' => 'required|integer|min:0',
            'type'    => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        // 设置好友消息未读数
        if ($data['type'] === 1) {
            $this->container->get(UnreadTalk::class)->del($this->uid(), $data['receive']);
        }

        return $this->response->success('success');
    }

    /**
     *  获取对话面板中的聊天记录
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getChatRecords(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'source'     => 'required|in:1,2',//消息来源（1：好友消息 2：群聊消息）
            'record_id'  => 'required|integer|min:0',
            'receive_id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data  = $validator->validated();
        $limit = 30;

        // 判断是否属于群成员
        if ($data['source'] === 2 && Group::isMember($data['receive_id'], $this->uid()) === false) {
            return $this->response->success('非群聊成员不能查看群聊信息...', [
                'rows'      => [],
                'record_id' => 0,
                'limit'     => $limit
            ]);
        }

        $result = $this->service->getChatRecords(
            $this->uid(),
            $data['receive_id'],
            $data['source'],
            $data['record_id'],
            $limit
        );

        return $this->response->success('success', [
            'rows'      => $result,
            'record_id' => $result ? end($result)['id'] : 0,
            'limit'     => $limit
        ]);
    }

    /**
     * 撤回聊天对话消息
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function revokeChatRecords(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'record_id' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        [$isTrue, $message, $data] = $this->service->revokeRecord($this->uid(), $data['record_id']);
        if ($isTrue) {
            //这里需要调用WebSocket推送接口
            Coroutine::create(function () use ($data)
            {
                $proxy = make(RevokeRecord::class);
                $proxy->process($data['id']);
            });
        }

        return $isTrue ? $this->response->success($message) : $this->response->error($message);
    }

    /**
     * 删除聊天记录
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function removeChatRecords(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'source'     => 'required|in:1,2',//消息来源（1：好友消息 2：群聊消息）
            'record_id'  => 'required|ids',
            'receive_id' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data       = $validator->validated();
        $record_ids = explode(',', $data['record_id']);

        $isTrue = $this->service->removeRecords(
            $this->uid(),
            $data['source'],
            $data['receive_id'],
            $record_ids
        );

        return $isTrue
            ? $this->response->success('删除成功...')
            : $this->response->fail('删除失败...');
    }

    /**
     * 转发聊天记录(待优化)
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function forwardChatRecords(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            //消息来源[1：好友消息 2：群聊消息]
            'source'       => 'required|in:1,2',
            //聊天记录ID，多个逗号拼接
            'records_ids'  => 'required',
            //接收者ID（好友ID或者群聊ID）
            'receive_id'   => 'required|integer|min:0',
            //转发方方式[1:逐条转发;2:合并转发]
            'forward_mode' => 'required|in:1,2',
            //            //转发的好友的ID
            //            'receive_user_ids' => 'array',
            //            //转发的群聊ID
            //            'receive_group_ids' => 'array',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data  = $validator->validated();
        $items = array_merge(
            array_map(static function ($friend_id)
            {
                return ['source' => 1, 'id' => $friend_id];
            }, (array)$data['receive_user_ids']),
            array_map(static function ($group_id)
            {
                return ['source' => 2, 'id' => $group_id];
            }, (array)$data['receive_group_ids'])
        );

        if ($data['forward_mode'] === 1) {//单条转发
            try {
                $ids = $this->service->forwardRecords($this->uid(), $data['receive_id'], $data['records_ids']);
            } catch (\Exception $e) {
            }
        } else {//合并转发
            $ids = $this->service->mergeForwardRecords($this->uid(), $data['receive_id'], $data['source'], $data['records_ids'], $items);
        }

        if (!$ids) {
            return $this->response->error('转发失败...');
        }

        if ($data['receive_user_ids']) {
            foreach ($data['receive_user_ids'] as $v) {
                $this->container->get(UnreadTalk::class)->setInc($v, $this->uid());
            }
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($ids)
        {
            $proxy = $this->container->get(ForwardChatRecords::class);
            $proxy->process($ids);
        });

        return $this->response->success('转发成功...');
    }

    /**
     * 获取转发记录详情
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getForwardRecords(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make($request->all(), [
            'records_id' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $rows = $this->service->getForwardRecords(
            $this->uid(),
            $data['records_id']
        );

        return $this->response->success('success', ['rows' => $rows]);
    }

    /**
     * 查询聊天记录
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     */
    public function findChatRecords(RequestInterface $request)
    {

    }

    public function searchChatRecords(RequestInterface $request)
    {

    }

    public function getRecordsContext(RequestInterface $request)
    {

    }

    public function sendImage(RequestInterface $request)
    {

    }

    public function sendFile(RequestInterface $request)
    {

    }

    public function sendCodeBlock(RequestInterface $request)
    {

    }

    public function sendEmoticon(RequestInterface $request)
    {

    }

}
