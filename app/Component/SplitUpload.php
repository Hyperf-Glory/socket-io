<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Component;

use App\Model\FileSplitUpload;
use Hyperf\HttpMessage\Upload\UploadedFile;
use League\Flysystem\Filesystem;

/**
 * Class SplitUpload.
 */
class SplitUpload
{
    // 文件拆分大小
    protected $splitSize;

    // 上传用户的用户ID
    protected $uid;

    /**
     * @var
     */
    protected $fileSystem;

    public function __construct(int $uid, $splitSize = 2 * 1024 * 1024, ?Filesystem $filesystem = null)
    {
        $this->splitSize = $splitSize;
        $this->uid = $uid;
        $this->fileSystem = is_null($filesystem) ? di(Filesystem::class) : $filesystem;
    }

    /**
     * 创建文件拆分相关信息.
     *
     * @param string $fileName 上传的文件名
     * @param string $fileSize 上传文件大小
     *
     * @throws \Exception
     * @return array|bool
     */
    public function createSplitInfo(string $fileName, string $fileSize)
    {
        $hash_name = implode('-', [create_short_code($fileName), uniqid('', false), random_int(10000000, 99999999)]);
        $split_num = (int) ceil($fileSize / $this->splitSize);

        $data = [];
        $data['file_type'] = 1;
        $data['user_id'] = $this->uid;
        $data['original_name'] = $fileName;
        $data['hash_name'] = $hash_name;
        $data['file_ext'] = pathinfo($fileName, PATHINFO_EXTENSION);
        $data['file_size'] = $fileSize;
        $data['upload_at'] = time();

        //文件拆分数量
        $data['split_num'] = $split_num;
        $data['split_index'] = $split_num;

        return FileSplitUpload::create($data) ? array_merge($data, ['split_size' => $this->splitSize]) : false;
    }

    /**
     * 判断拆分文件的大小是否合理.
     *
     * @param $fileSize
     */
    public function checkSplitSize($fileSize): bool
    {
        return $fileSize > $this->splitSize;
    }

    /**
     * 保存拆分文件.
     *
     * @param UploadedFile $file 文件信息
     * @param string $hashName 上传临时问价hash名
     * @param int $split_index 当前拆分文件索引
     * @param int $fileSize 文件大小
     */
    public function saveSplitFile(UploadedFile $file, string $hashName, int $split_index, int $fileSize): bool
    {
        /**
         * @var FileSplitUpload $fileInfo
         */
        $fileInfo = FileSplitUpload::select(['id', 'original_name', 'split_num', 'file_ext'])->where('user_id', $this->uid)->where('hash_name', $hashName)->where('file_type', 1)->first();
        if (! $fileInfo) {
            return false;
        }

        // 保存文件名及保存文件相对目录
        $fileName = "{$hashName}_{$split_index}_{$fileInfo->file_ext}.tmp";
        $save_dir = "tmp/{$hashName}";

        // 判断上传目录是否存在(不存在则创建)
        if (! is_dir($save_dir)) {
            $this->fileSystem->createDir($save_dir);
        }
        $stream = fopen($file->getRealPath(), 'rb+');
        $save_path = trim($save_dir . '/' . $fileName, '/');
        // 保存文件
        if (! $this->fileSystem->put($save_path, $stream)) {
            fclose($stream);
            return false;
        }
        fclose($stream);
        $info = FileSplitUpload::where('user_id', $this->uid)->where('hash_name', $hashName)->where('split_index', $split_index)->first();
        if (! $info) {
            return FileSplitUpload::create([
                'user_id' => $this->uid,
                'file_type' => 2,
                'hash_name' => $hashName,
                'original_name' => $fileInfo->original_name,
                'split_index' => $split_index,
                'split_num' => $fileInfo->split_num,
                'save_dir' => $save_path,
                'file_ext' => $fileInfo->file_ext,
                'file_size' => $fileSize,
                'upload_at' => time(),
            ]) ? true : false;
        }

        return true;
    }

    /**
     * 合并拆分文件.
     *
     * @return array|bool
     */
    public function fileMerge(string $hash_name)
    {
        /**
         * @var FileSplitUpload $fileInfo
         */
        $fileInfo = FileSplitUpload::select(['id', 'original_name', 'split_num', 'file_ext', 'file_size'])->where('user_id', $this->uid)->where('hash_name', $hash_name)->where('file_type', 1)->first();
        if (! $fileInfo) {
            return false;
        }

        $files = FileSplitUpload::where('user_id', $this->uid)->where('hash_name', $hash_name)->where('file_type', 2)->orderBy('split_index', 'asc')->get(['split_index', 'save_dir'])->toArray();
        if (! $files) {
            return false;
        }

        if (count($files) !== $fileInfo->split_num) {
            return false;
        }

        $dir = config('filesystem.storage.local.root');
        $fileMerge = "tmp/{$hash_name}/{$fileInfo->original_name}.tmp";
        foreach ($files as $file) {
            file_put_contents($dir . '/' . $fileMerge, file_get_contents($dir . '/' . $file['save_dir']), FILE_APPEND);
        }

        FileSplitUpload::select(['id', 'original_name', 'split_num', 'file_ext', 'file_size'])->where('user_id', $this->uid)->where('hash_name', $hash_name)->where('file_type', 1)->update(['save_dir' => $fileMerge]);
        return [
            'path' => $fileMerge,
            'tmp_file_name' => "{$fileInfo->original_name}.tmp",
            'original_name' => $fileInfo->original_name,
            'file_size' => $fileInfo->file_size,
        ];
    }
}
