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

use App\Component\SplitUpload;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

class UploadController extends AbstractController
{
    public function fileStream(Filesystem $filesystem): ResponseInterface
    {
        $fileStream = $this->request->post('fileStream', '');

        $data = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $fileStream));
        $path = 'media/images/avatar/' . date('Ymd') . '/' . uniqid('', false) . date('His') . '.png';

        if (! $filesystem->put($path, $data)) {
            return $this->response->error('文件保存失败');
        }

        return $this->response->success('文件上传成功...', ['avatar' => get_media_url($path)]);
    }

    /**
     * @throws \Exception
     */
    public function getFileSplitInfo(): ResponseInterface
    {
        if (! $this->request->has(['file_name', 'file_size'])) {
            return $this->response->parmasError();
        }

        $logic = new SplitUpload($this->uid());
        $data = $logic->createSplitInfo($this->request->input('file_name'), $this->request->input('file_size'));

        return $data ? $this->response->success('success', $data) : $this->response->error('获取文件拆分信息失败...');
    }

    public function fileSubareaUpload(): ResponseInterface
    {
        $file = $this->request->file('file');

        $params = ['name', 'hash', 'ext', 'size', 'split_index', 'split_num'];
        if (! $this->request->has($params) || ! $file->isValid()) {
            return $this->response->parmasError();
        }

        $info = $this->request->inputs($params);
        $fileSize = $file->getSize();

        $logic = new SplitUpload($this->uid());

        if (! $uploadRes = $logic->saveSplitFile($file, $info['hash'], (int) $info['split_index'], $fileSize)) {
            return $this->response->error('上传文件失败...');
        }

        if (((int) $info['split_index'] + 1) === (int) $info['split_num']) {
            $fileInfo = $logic->fileMerge($info['hash']);
            if (! $fileInfo) {
                return $this->response->error('上传文件失败...');
            }

            return $this->response->success('文件上传成功...', [
                'is_file_merge' => true,
                'hash' => $info['hash'],
            ]);
        }

        return $this->response->success('文件上传成功...', ['is_file_merge' => false]);
    }
}
