<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\Controller;

use App\Helper\ValidateHelper;
use App\Model\ChatRecords;
use App\Model\ChatRecordsFile;
use App\Model\UsersGroup;
use Hyperf\Filesystem\FilesystemFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DownloadController
 * @package App\Controller
 * @TODO 待解决下载文件不完整的问题
 */
class DownloadController extends AbstractController
{
    public function userChatFile(): ResponseInterface
    {
        $crId = (int) $this->request->input('cr_id', 0);
        $uid = $this->uid();

        if (! ValidateHelper::isInteger($crId)) {
            return $this->response->error('文件下载失败...');
        }

        /**
         * @var ChatRecords $recordsInfo
         */
        $recordsInfo = ChatRecords::select(['msg_type', 'source', 'user_id', 'receive_id'])->where('id', $crId)->first();
        if (! $recordsInfo) {
            return $this->response->error('文件不存在...');
        }

        //判断消息是否是当前用户发送(如果是则跳过权限验证)
        if ($recordsInfo->user_id !== $uid) {
            if ($recordsInfo->source === 1) {
                if ($recordsInfo->receive_id !== $uid) {
                    return $this->response->error('非法请求...');
                }
            } elseif (! UsersGroup::isMember($recordsInfo->receive_id, $uid)) {
                return $this->response->error('非法请求...');
            }
        }

        /**
         * @var ChatRecordsFile $fileInfo
         */
        $fileInfo = ChatRecordsFile::select(['save_dir', 'original_name'])->where('record_id', $crId)->first();
        if (! $fileInfo) {
            return $this->response->error('文件不存在或没有下载权限...');
        }

        $factory = di(FilesystemFactory::class)->get('qiniu');
        if ($factory->has($fileInfo->save_dir)) {
            $dir = config('file.storage.local.root');
            $contents = $factory->read($fileInfo->save_dir);
            $fileSystem = di(FilesystemFactory::class)->get('local');
            if ($fileSystem->has($fileInfo->save_dir)) {
                return $this->response->download($dir . '/' . $fileInfo->save_dir, $fileInfo->original_name);
            }
            $fileSystem->write($fileInfo->save_dir, $contents);
            return $this->response->download($dir . '/' . $fileInfo->save_dir, $fileInfo->original_name);
        }
        return $this->response->error('文件不存在...');
    }

}
