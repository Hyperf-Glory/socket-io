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
namespace App\Controller;

use App\Cache\LastMsgCache;
use App\Component\Proxy;
use App\Helper\ArrayHelper;
use App\Helper\StringHelper;
use App\Helper\ValidateHelper;
use App\Model\ChatRecords;
use App\Model\ChatRecordsCode;
use App\Model\ChatRecordsFile;
use App\Model\EmoticonDetail;
use App\Model\FileSplitUpload;
use App\Model\Users;
use App\Model\UsersChatList;
use App\Model\UsersFriends;
use App\Model\UsersGroup;
use App\Service\TalkService;
use App\Services\Common\UnreadTalk;
use Exception;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Coroutine;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use RuntimeException;

class TalkController extends AbstractController
{
    private $service;

    public function __construct(TalkService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * 获取用户对话列表.
     */
    public function list(): PsrResponseInterface
    {
        $result = di(UnreadTalk::class)->getAll($this->uid());
        if ($result) {
            $this->service->updateUnreadTalkList($this->uid(), $result);
        }
        $rows = $this->service->talks($this->uid());
        if ($rows) {
            $rows = ArrayHelper::sortByField($rows, 'updated_at');
        }
        return $this->response->success('success', $rows);
    }

    /**
     * 新增对话列表.
     */
    public function create(): PsrResponseInterface
    {
        $type = (int) $this->request->post('type', 1); //创建的类型
        $receive_id = (int) $this->request->post('receive_id', 0); //接收者ID
        if (! in_array($type, [1, 2], true) || ! ValidateHelper::isInteger($receive_id)) {
            return $this->response->parmasError();
        }

        if ($type === 1) {

            if (! UsersFriends::isFriend($this->uid(), $receive_id)) {
                return $this->response->fail(305, '暂不属于好友关系，无法进行聊天...');
            }
        } elseif (! UsersGroup::isMember($receive_id, $this->uid())) {
            return $this->response->fail(305, '暂不属于群成员，无法进行群聊 ...');
        }

        $result = UsersChatList::addItem($this->uid(), $receive_id, $type);
        if (! $result) {
            return $this->response->error('创建失败...');
        }

        $data = [
            'id' => $result['id'],
            'type' => $result['type'],
            'group_id' => $result['group_id'],
            'friend_id' => $result['friend_id'],
            'is_top' => 0,
            'msg_text' => '',
            'not_disturb' => 0,
            'online' => 1,
            'name' => '',
            'remark_name' => '',
            'avatar' => '',
            'unread_num' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($result['type'] === 1) {
            $data['unread_num'] = di(UnreadTalk::class)->get($this->uid(), $result['friend_id']);

            /**
             * @var Users $userInfo
             */
            $userInfo = Users::where('id', $this->uid())->first(['nickname', 'avatar']);
            $data['name'] = $userInfo->nickname;
            $data['avatar'] = $userInfo->avatar;
        } elseif ($result['type'] === 2) {
            /**
             * @var UsersGroup $groupInfo
             */
            $groupInfo = UsersGroup::where('id', $result['group_id'])->first(['group_name', 'avatar']);
            $data['name'] = $groupInfo->group_name;
            $data['avatar'] = $groupInfo->avatar;
        }

        $records = LastMsgCache::get($result['type'] === 1 ? (int) $result['friend_id'] : (int) $result['group_id'], $result['type'] === 1 ? $this->uid() : 0);
        if ($records) {
            $data['msg_text'] = $records['text'];
            $data['updated_at'] = $records['created_at'];
        }
        return $this->response->success('创建成功...', ['talkItem' => $data]);
    }

    /**
     *  删除对话列表.
     */
    public function delete(): PsrResponseInterface
    {
        $list_id = (int) $this->request->post('list_id', 0);
        if (! ValidateHelper::isInteger($list_id)) {
            return $this->response->parmasError();
        }

        $bool = UsersChatList::delItem($this->uid(), $list_id);
        return $bool ? $this->response->success('操作完成...') : $this->response->error('操作失败...');
    }

    /**
     * 对话列表置顶.
     */
    public function topping(): PsrResponseInterface
    {
        $list_id = (int) $this->request->post('list_id', 0);
        $type = (int) $this->request->post('type', 0);
        if (! ValidateHelper::isInteger($list_id) || ! in_array($type, [1, 2], true)) {
            return $this->response->parmasError();
        }
        $bool = UsersChatList::topItem($this->uid(), $list_id, $type === 1);
        return $bool ? $this->response->success('操作完成...') : $this->response->error('操作失败...');
    }

    /**
     * 设置消息免打扰状态
     */
    public function setNotDisturb(): PsrResponseInterface
    {
        $type = (int) $this->request->post('type', 0);
        $receive_id = (int) $this->request->post('receive_id', 0);
        $not_disturb = (int) $this->request->post('not_disturb', 0);

        if (! ValidateHelper::isInteger($receive_id) || ! in_array($type, [1, 2], true) || ! in_array($not_disturb, [0, 1], true)) {
            return $this->response->parmasError();
        }

        $bool = UsersChatList::notDisturbItem($this->uid(), $receive_id, $type, $not_disturb);

        return $bool ? $this->response->success('设置成功...') : $this->response->error('设置失败...');
    }

    /**
     * 更新对话列表未读数.
     *
     */
    public function updateUnreadNum(): PsrResponseInterface
    {
        $type = (int) $this->request->post('type', 0);
        $receive_id = (int) $this->request->post('receive', 0);

        if ($type === 1) {
            di(UnreadTalk::class)->del($this->uid(), $receive_id);
        }

        return  $this->response->success('success...');
    }

    /**
     * 获取对话面板中的聊天记录.
     */
    public function getChatRecords(): PsrResponseInterface
    {
        $user_id = (int) $this->uid();
        $receive_id = (int) $this->request->input('receive_id', 0);
        $source = (int) $this->request->input('source', 0);
        $record_id = (int) $this->request->input('record_id', 0);
        $limit = 30;

        if (! ValidateHelper::isInteger($receive_id) || ! ValidateHelper::isInteger($source) || ! ValidateHelper::isInteger($record_id)) {
            return $this->response->parmasError();
        }

        //判断是否属于群成员
        if ($source === 2 && UsersGroup::isMember($receive_id, $user_id) === false) {
            return $this->response->success('非群聊成员不能查看群聊信息', [
                'rows' => [],
                'record_id' => 0,
                'limit' => $limit,
            ]);
        }

        $result = $this->service->getChatRecords($user_id, $receive_id, $source, $record_id, $limit);

        return $this->response->success('success', [
            'rows' => $result,
            'record_id' => $result ? $result[count($result) - 1]['id'] : 0,
            'limit' => $limit,
        ]);
    }

    /**
     * 撤回聊天对话消息.
     */
    public function revokeChatRecords(): PsrResponseInterface
    {
        $user_id = (int) $this->uid();
        $record_id = (int) $this->request->input('record_id', 0);
        if (! ValidateHelper::isInteger($record_id)) {
            return $this->response->parmasError();
        }

        [$isTrue, $message, $data] = $this->service->revokeRecord($user_id, $record_id);
        if ($isTrue) {
            //这里需要调用WebSocket推送接口
            Coroutine::create(function () use ($data) {
                $proxy = $this->container->get(Proxy::class);
                $proxy->revokeRecords($data['id']);
            });
        }

        return $isTrue ? $this->response->success($message) : $this->response->error($message);
    }

    /**
     * 删除聊天记录.
     */
    public function removeChatRecords(): PsrResponseInterface
    {
        $user_id = $this->uid();

        //消息来源（1：好友消息 2：群聊消息）
        $source = (int) $this->request->post('source', 0);

        //接收者ID（好友ID或者群聊ID）
        $receive_id = (int) $this->request->post('receive_id', 0);

        //消息ID
        $record_ids = explode(',', $this->request->input('record_id', ''));
        if (empty($record_ids) || ! in_array($source, [1, 2], true) || ! ValidateHelper::isInteger($receive_id)) {
            return $this->response->parmasError();
        }

        $isTrue = $this->service->removeRecords($user_id, $source, $receive_id, $record_ids);
        return $isTrue ? $this->response->success('删除成功...') : $this->response->error('删除失败...');
    }

    /**
     * 转发聊天记录(待优化).
     *
     * @throws \Exception
     */
    public function forwardChatRecords(): PsrResponseInterface
    {
        $user_id = $this->uid();
        //转发方方式
        $forward_mode = (int) $this->request->post('forward_mode', 0);
        //消息来源（1：好友消息 2：群聊消息）
        $source = (int) $this->request->post('source', 1);
        //接收者ID（好友ID或者群聊ID）
        $receive_id = (int) $this->request->post('receive_id', 0);
        //转发的记录IDS
        $records_ids = (array) $this->request->post('records_ids', []);
        //转发的好友的ID
        $receive_user_ids = (array) $this->request->post('receive_user_ids', []);
        //转发的群聊ID
        $receive_group_ids = (int) $this->request->post('receive_group_ids', []);

        if (empty($records_ids) || empty($receive_user_ids) || empty($receive_group_ids) || ! in_array($forward_mode, [1, 2], true) || ! in_array($source, [1, 2], true) || ! ValidateHelper::isInteger($receive_id)) {
            return $this->response->parmasError();
        }

        $items = array_merge(
            array_map(static function ($friend_id) {
                return ['source' => 1, 'id' => $friend_id];
            }, (array) $receive_user_ids),
            array_map(static function ($group_id) {
                return ['source' => 2, 'id' => $group_id];
            }, (array) $receive_group_ids)
        );

        if ($forward_mode === 1) {//单条转发
            $ids = $this->service->forwardRecords($user_id, $receive_id, $records_ids);
        } else {//合并转发
            $ids = $this->service->mergeForwardRecords($user_id, $receive_id, $source, $records_ids, $items);
        }

        if (! $ids) {
            return $this->response->error('转发失败...');
        }

        if ($receive_user_ids) {
            foreach ($receive_user_ids as $v) {
                di(UnreadTalk::class)->setInc($v, $user_id);
            }
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($ids) {
            $proxy = $this->container->get(Proxy::class);
            $proxy->forwardChatRecords($ids);
        });

        return $this->response->success('转发成功...');
    }

    /**
     * 获取转发记录详情.
     */
    public function getForwardRecords(): PsrResponseInterface
    {
        $records_id = (int) $this->request->post('records_id', 0);
        if (! ValidateHelper::isInteger($records_id)) {
            return $this->response->parmasError();
        }

        $rows = $this->service->getForwardRecords($this->uid(), $records_id);
        return $this->response->success('success', ['rows' => $rows]);
    }

    /**
     * 查询聊天记录.
     */
    public function findChatRecords(): PsrResponseInterface
    {
        $user_id = $this->uid();
        $receive_id = (int) $this->request->input('receive_id', 0);
        $source = (int) $this->request->input('source', 0);
        $record_id = (int) $this->request->input('record_id', 0);
        $msg_type = (int) $this->request->input('msg_type', 0);
        $limit = 30;

        if (! ValidateHelper::isInteger($receive_id) || ! ValidateHelper::isInteger($source) || ! ValidateHelper::isInteger($record_id, true)) {
            return $this->response->parmasError();
        }

        //判断是否属于群成员
        if ($source === 2 && UsersGroup::isMember($receive_id, $user_id) === false) {
            return $this->response->success('非群聊成员不能查看群聊信息', [
                'rows' => [],
                'record_id' => 0,
                'limit' => $limit,
            ]);
        }

        if (in_array($msg_type, [1, 2, 4, 5], true)) {
            $msg_type = [$msg_type];
        } else {
            $msg_type = [1, 2, 4, 5];
        }

        $result = $this->service->getChatRecords($user_id, $receive_id, $source, $record_id, $limit, $msg_type);
        return $this->response->success('success', [
            'rows' => $result,
            'record_id' => $result ? $result[count($result) - 1]['id'] : 0,
            'limit' => $limit,
        ]);
    }

    /**
     * 搜索聊天记录（待优化）.
     */
    public function searchChatRecords(): PsrResponseInterface
    {
        $receive_id = (int) $this->request->input('receive_id', 0);
        $source = (int) $this->request->input('source', 0);
        $keywords = (int) $this->request->input('keywords', '');
        $date = $this->request->input('date', '');
        $page = (int) $this->request->input('page', 1);

        if (! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true) || ! ValidateHelper::isInteger($page)) {
            return $this->response->parmasError();
        }

        $params = [];
        if (! empty($keywords)) {
            $params['keywords'] = addslashes($keywords);
        }

        if (! empty($date)) {
            $params['date'] = $date;
        }

        return $this->response->success('success', []);
    }

    /**
     * 获取聊天记录上下文数据（待优化）.
     */
    public function getRecordsContext(): PsrResponseInterface
    {
        $receive_id = (int) $this->request->input('receive_id', 0);
        $source = (int) $this->request->input('source', 0);
        $record_id = (int) $this->request->post('record_id', 0);
        $find_mode = (int) $this->request->post('find_mode', 1);

        if (! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true) || ! ValidateHelper::isInteger($record_id) || ! in_array($find_mode, [1, 2], true)) {
            return $this->response->parmasError();
        }

        return $this->response->success('success', []);
    }

    /**
     * 上传聊天对话图片（待优化）.
     */
    public function sendImage(Filesystem $fileSystem): PsrResponseInterface
    {
        $file = $this->request->file('img');
        $receive_id = (int) $this->request->post('receive_id', 0);
        $source = (int) $this->request->post('source', 0);

        if (! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true)) {
            return $this->response->parmasError();
        }

        $user_id = $this->uid();
        if (! $file->isValid()) {
            return $this->response->parmasError('请求参数错误');
        }

        $ext = $file->getExtension();
        if (! in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            return $this->response->error('图片格式错误，目前仅支持jpg、png、jpeg、gif和webp');
        }

        $imgInfo = getimagesize($file->getRealPath());
        $filename = create_image_name($ext, $imgInfo[0], $imgInfo[1]);
        $stream = fopen($file->getRealPath(), 'rb+');
        $save_path = 'media/images/talks/' . date('Ymd') . $filename;
        //保存图片
        if (! $fileSystem->put($save_path, $stream)) {
            fclose($stream);
            return $this->response->error('图片上传失败');
        }

        fclose($stream);

        Db::beginTransaction();
        try {
            $insert = ChatRecords::create([
                'source' => $source,
                'msg_type' => 2,
                'user_id' => $user_id,
                'receive_id' => $receive_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $insert) {
                throw new RuntimeException('插入聊天记录失败...');
            }

            $result = ChatRecordsFile::create([
                'record_id' => $insert->id,
                'user_id' => $this->uid(),
                'file_type' => 1,
                'file_suffix' => $file->getBasename(),
                'file_size' => $file->getSize(),
                'save_dir' => $save_path,
                'original_name' => $file->getClientFilename(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (! $result) {
                throw new RuntimeException('插入聊天记录(文件消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return $this->response->error('图片上传失败');
        }

        // 设置好友消息未读数
        if ($insert->source === 1) {
            di(UnreadTalk::class)->setInc($insert->receive_id, $insert->user_id);
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($insert) {
            $proxy = $this->container->get(Proxy::class);
            $proxy->pushTalkMessage($insert->id);
        });

        return $this->response->success('图片上传成功...');
    }

    /**
     * 发送代码块消息.
     */
    public function sendCodeBlock(): PsrResponseInterface
    {
        $code = $this->request->post('code', '');
        $lang = $this->request->post('lang', '');
        $receive_id = (int) $this->request->post('receive_id', 0);
        $source = (int) $this->request->post('source', 0);

        if (empty($code) || empty($lang) || ! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true)) {
            return $this->response->parmasError();
        }

        $user_id = $this->uid();
        Db::beginTransaction();
        try {
            $insert = ChatRecords::create([
                'source' => $source,
                'msg_type' => 5,
                'user_id' => $user_id,
                'receive_id' => $receive_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $insert) {
                throw new RuntimeException('插入聊天记录失败...');
            }

            $result = ChatRecordsCode::create([
                'record_id' => $insert->id,
                'user_id' => $user_id,
                'code_lang' => $lang,
                'code' => $code,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                throw new RuntimeException('插入聊天记录(代码消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return $this->response->error('消息发送失败...');
        }

        // 设置好友消息未读数
        if ($insert->source === 1) {
            di(UnreadTalk::class)->setInc($insert->receive_id, $insert->user_id);
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($insert) {
            $proxy = $this->container->get(Proxy::class);
            $proxy->pushTalkMessage($insert->id);
        });

        return $this->response->success('消息发送成功...');
    }

    /**
     * 发送文件消息.
     *
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function sendFile(Filesystem $fileSystem): PsrResponseInterface
    {
        $hash_name = $this->request->post('hash_name', '');
        $receive_id = (int) $this->request->post('receive_id', 0);
        $source = (int) $this->request->post('source', 0);

        if (empty($hash_name) || ! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true)) {
            return $this->response->parmasError();
        }

        $user_id = $this->uid();
        /**
         * @var FileSplitUpload $file
         */
        $file = FileSplitUpload::where('user_id', $user_id)->where('hash_name', $hash_name)->where('file_type', 1)->first();
        if (! $file || empty($file->save_dir)) {
            return $this->response->fail(302, '文件不存在...');
        }

        $file_hash_name = uniqid('', false) . StringHelper::randString() . '.' . $file->file_ext;
        $save_dir = 'files/talks/' . date('Ymd') . '/' . $file_hash_name;

        if (! $fileSystem->copy($file->save_dir, $save_dir)) {
            return $this->response->fail(303, '文件上传失败...');
        }

        Db::beginTransaction();
        try {
            $insert = ChatRecords::create([
                'source' => $source,
                'msg_type' => 2,
                'user_id' => $user_id,
                'receive_id' => $receive_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $insert) {
                throw new RuntimeException('插入聊天记录失败...');
            }

            $result = ChatRecordsFile::create([
                'record_id' => $insert->id,
                'user_id' => $user_id,
                'file_source' => 1,
                'file_type' => 4,
                'original_name' => $file->original_name,
                'file_suffix' => $file->file_ext,
                'file_size' => $file->file_size,
                'save_dir' => $save_dir,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                throw new RuntimeException('插入聊天记录(代码消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            $fileSystem->delete($save_dir);
            return $this->response->error('消息发送失败...');
        }

        // 设置好友消息未读数
        if ($insert->source === 1) {
            di(UnreadTalk::class)->setInc($insert->receive_id, $insert->user_id);
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($insert) {
            $proxy = $this->container->get(Proxy::class);
            $proxy->pushTalkMessage($insert->id);
        });

        return $this->response->success('消息发送成功...');
    }

    /**
     * 发送表情包.
     */
    public function sendEmoticon(): PsrResponseInterface
    {
        $emoticon_id = $this->request->post('emoticon_id', 0);
        $receive_id = (int) $this->request->post('receive_id', 0);
        $source = (int) $this->request->post('source', 0);

        if (! ValidateHelper::isInteger($emoticon_id) || ! ValidateHelper::isInteger($receive_id) || ! in_array($source, [1, 2], true)) {
            return $this->ajaxParamError();
        }

        $user_id = $this->uid();
        /**
         * @var EmoticonDetail $emoticon
         */
        $emoticon = EmoticonDetail::where('id', $emoticon_id)->where('user_id', $user_id)->first([
            'url',
            'file_suffix',
            'file_size',
        ]);

        if (! $emoticon) {
            return $this->response->error('发送失败...');
        }

        Db::beginTransaction();
        try {
            $insert = ChatRecords::create([
                'source' => $source,
                'msg_type' => 2,
                'user_id' => $user_id,
                'receive_id' => $receive_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $insert) {
                throw new RuntimeException('插入聊天记录失败...');
            }

            $result = ChatRecordsFile::create([
                'record_id' => $insert->id,
                'user_id' => $this->uid(),
                'file_type' => 1,
                'file_suffix' => $emoticon->file_suffix,
                'file_size' => $emoticon->file_size,
                'save_dir' => $emoticon->url,
                'original_name' => '表情',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                throw new RuntimeException('插入聊天记录(文件消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return $this->response->error('表情发送失败');
        }

        // 设置好友消息未读数
        if ($insert->source === 1) {
            di(UnreadTalk::class)->setInc($insert->receive_id, $insert->user_id);
        }

        //这里需要调用WebSocket推送接口
        Coroutine::create(function () use ($insert) {
            $proxy = $this->container->get(Proxy::class);
            $proxy->pushTalkMessage($insert->id);
        });

        return $this->response->success('表情发送成功...');
    }
}
