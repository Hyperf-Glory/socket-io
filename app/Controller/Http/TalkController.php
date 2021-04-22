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
use Hyperf\HttpServer\Contract\RequestInterface;
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
             * @var Users $userInfo
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

    public function updateUnreadNum(RequestInterface $request)
    {

    }

    public function getChatRecords(RequestInterface $request)
    {

    }

    public function revokeChatRecords(RequestInterface $request)
    {

    }

    public function removeChatRecords(RequestInterface $request)
    {

    }

    public function forwardChatRecords(RequestInterface $request)
    {

    }

    public function getForwardRecords(RequestInterface $request)
    {

    }

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
