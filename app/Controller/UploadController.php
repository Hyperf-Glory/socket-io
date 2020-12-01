<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\SplitUpload;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

class UploadController extends AbstractController
{
    /**
     * @param \League\Flysystem\Filesystem $filesystem
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fileStream(Filesystem $filesystem) : ResponseInterface
    {
        $fileStream = $this->request->post('fileStream', '');

        $data = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $fileStream));
        $path = 'media/images/avatar/' . date('Ymd') . '/' . uniqid('', false) . date('His') . '.png';

        if (!$filesystem->put($path, $data)) {
            return $this->response->error('文件保存失败');
        }

        return $this->response->success('文件上传成功...', ['avatar' => get_media_url($path)]);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function getFileSplitInfo() : ResponseInterface
    {
        if (!$this->request->has(['file_name', 'file_size'])) {
            return $this->response->parmasError();
        }

        $logic = new SplitUpload($this->uid());
        $data  = $logic->createSplitInfo($this->request->post('file_name'), $this->request->post('file_size'));

        return $data ? $this->response->success('success', $data) : $this->response->error('获取文件拆分信息失败...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fileSubareaUpload() : ResponseInterface
    {
        $file = $this->request->file('file');

        $params = ['name', 'hash', 'ext', 'size', 'split_index', 'split_num'];
        if (!$this->request->has($params) || !$file->isValid()) {
            return $this->response->parmasError();
        }

        $info     = $this->request->inputs($params);
        $fileSize = $file->getSize();

        $logic = new SplitUpload($this->uid());

        if (!$uploadRes = $logic->saveSplitFile($file, $info['hash'], $info['split_index'], $fileSize)) {
            return $this->response->error('上传文件失败...');
        }

        if (($info['split_index'] + 1) === $info['split_num']) {
            $fileInfo = $logic->fileMerge($info['hash']);
            if (!$fileInfo) {
                return $this->response->error('上传文件失败...');
            }

            return $this->response->success('文件上传成功...', [
                'is_file_merge' => true,
                'hash'          => $info['hash']
            ]);
        }

        return $this->response->success('文件上传成功...', ['is_file_merge' => false]);
    }

}
