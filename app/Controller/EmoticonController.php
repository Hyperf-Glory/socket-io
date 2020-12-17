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

use App\Helper\ValidateHelper;
use App\Model\Emoticon;
use App\Model\EmoticonDetail;
use App\Services\EmoticonService;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EmoticonController.
 * @TODO 表情包管理待测试(测试完成上线)
 */
class EmoticonController extends AbstractController
{
    /**
     * @var \App\Services\EmoticonService
     */
    private $service;

    public function __construct(EmoticonService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * 获取用户表情包列表.
     */
    public function getUserEmoticon(): ResponseInterface
    {
        $emoticonList = [];
        $user_id = $this->uid();

        if ($ids = $this->service->getInstallIds($user_id)) {
            $items = Emoticon::whereIn('id', $ids)->get(['id', 'name', 'url']);
            foreach ($items as $item) {
                $emoticonList[] = [
                    'emoticon_id' => $item->id,
                    'url' => get_media_url($item->url),
                    'name' => $item->name,
                    'list' => $this->emoticonService->getDetailsAll([
                        ['emoticon_id', '=', $item->id],
                        ['user_id', '=', 0],
                    ]),
                ];
            }
        }

        return $this->response->success('success', [
            'sys_emoticon' => $emoticonList,
            'collect_emoticon' => $this->service->getDetailsAll([
                ['emoticon_id', '=', 0],
                ['user_id', '=', $user_id],
            ]),
        ]);
    }

    /**
     *  获取系统表情包.
     */
    public function getSystemEmoticon(): ResponseInterface
    {
        $items = Emoticon::get(['id', 'name', 'url'])->toArray();
        if ($items) {
            $ids = $this->service->getInstallIds($this->uid());

            array_walk($items, static function (&$item) use ($ids) {
                $item['status'] = in_array($item['id'], $ids, true) ? 1 : 0;
                $item['url'] = get_media_url($item['url']);
            });
        }

        return $this->response->success('success', $items);
    }

    public function setUserEmoticon(): ResponseInterface
    {
        $emoticon_id = $this->request->post('emoticon_id');
        $type = $this->request->post('type');
        if (! ValidateHelper::isInteger($emoticon_id) || ! in_array($type, [1, 2], true)) {
            return $this->response->parmasError();
        }

        $user_id = $this->uid();
        if ($type === 2) {//移除表情包
            $bool = $this->service->removeSysEmoticon($user_id, $emoticon_id);
            return $bool ? $this->response->success('移除表情包成功...') : $this->response->error('移除表情包失败...');
        }  //添加表情包
        /**
         * @var Emoticon $emoticonInfo
         */
        $emoticonInfo = Emoticon::where('id', $emoticon_id)->first(['id', 'name', 'url']);
        if (! $emoticonInfo) {
            return $this->response->error('添加表情包失败...');
        }

        if (! $this->emoticonService->installSysEmoticon($user_id, $emoticon_id)) {
            return $this->response->error('添加表情包失败...');
        }

        $data = [
            'emoticon_id' => $emoticonInfo->id,
            'url' => get_media_url($emoticonInfo->url),
            'name' => $emoticonInfo->name,
            'list' => $this->service->getDetailsAll([
                ['emoticon_id', '=', $emoticonInfo->id],
            ]),
        ];

        return $this->response->success('添加表情包成功', $data);
    }

    /**
     * 收藏聊天图片的我的表情包.
     */
    public function collectEmoticon(): ResponseInterface
    {
        $id = $this->request->post('record_id');
        if (! ValidateHelper::isInteger($id)) {
            return $this->response->parmasError();
        }

        [$bool, $data] = $this->service->collect($this->uid(), $id);

        return $bool ? $this->response->success('success', [
            'emoticon' => $data,
        ]) : $this->response->error('添加表情失败');
    }

    /**
     * 自定义上传表情包.
     */
    public function uploadEmoticon(Filesystem $filesystem): ResponseInterface
    {
        $file = $this->request->file('emoticon');
        if (! $file->isValid()) {
            return $this->response->error('图片上传失败，请稍后再试...');
        }

        $ext = $file->getExtension();
        if (! in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            return $this->response->error('图片格式错误，目前仅支持jpg、png、jpeg、gif和webp');
        }

        $imgInfo = getimagesize($file->getRealPath());
        $filename = create_image_name($ext, $imgInfo[0], $imgInfo[1]);
        $save_path = 'media/images/emoticon/' . date('Ymd') . '/' . $filename;
        $stream = fopen($file->getRealPath(), 'rb+');
        if (! $filesystem->put($save_path, $stream)) {
            return $this->response->error('图片上传失败');
        }

        $result = EmoticonDetail::create([
            'user_id' => $this->uid(),
            'url' => $save_path,
            'file_suffix' => $ext,
            'file_size' => $file->getSize(),
            'created_at' => time(),
        ]);

        return $result ? $this->response->success('success', [
            'media_id' => $result->id,
            'src' => get_media_url($result->url),
        ]) : $this->response->error('表情包上传失败...');
    }

    /**
     * 移除收藏的表情包.
     */
    public function delCollectEmoticon(): ResponseInterface
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->response->parmasError();
        }

        $ids = explode(',', trim($ids));
        if (empty($ids)) {
            return $this->response->parmasError();
        }

        return $this->service->deleteCollect($this->uid(), $ids) ?
            $this->response->success('success') :
            $this->response->error('fail');
    }
}
