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
namespace App\Helper;

class DirectoryHelper
{
    /**
     * 创建深层目录.
     *
     * @param string $dir 路径
     * @param int $mode 权限模式
     */
    public static function mkdirDeep(string $dir, int $mode = 0766): bool
    {
        if ($dir === '') {
            return false;
        }
        if (is_dir($dir) && @chmod($dir, $mode)) {
            return true;
        }
        if (mkdir($dir, $mode, true) || is_dir($dir)) { //第三个参数为true即可以创建多级目录
            return true;
        }

        return false;
    }

    /**
     * 遍历路径获取文件树.
     *
     * @param string $path 路径
     * @param string $type 获取类型:all-所有,dir-仅目录,file-仅文件
     * @param bool $recursive 是否递归
     */
    public static function getFileTree(string $path, string $type = 'all', bool $recursive = true): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $tree = [];
        // '{.,*}*' 相当于 '.*'(搜索.开头的隐藏文件)和'*'(搜索正常文件)
        foreach (glob($path . '/{.,*}*', GLOB_BRACE) as $single) {
            if (is_dir($single)) {
                $file = str_replace($path . '/', '', $single);
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if ($type !== 'file') {
                    $tree[] = $single;
                }

                if ($recursive) {
                    $tree = array_merge(...self::getFileTree($single, $type, $recursive));
                }
            } elseif ($type !== 'dir') {
                $tree[] = $single;
            }
        }

        return $tree;
    }

    /**
     * 获取目录大小,单位[字节].
     */
    public static function getDirSize(string $path): int
    {
        $size = 0;
        if ($path === '' || ! is_dir($path)) {
            return $size;
        }

        $dh = @opendir($path); //比dir($path)快
        while (($file = @readdir($dh)) !== false) {
            if ($file !== '.' and $file !== '..') {
                $fielpath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fielpath)) {
                    $size += self::getDirSize($fielpath);
                } else {
                    $size += filesize($fielpath);
                }
            }
        }
        @closedir($dh);
        return $size;
    }

    /**
     * 拷贝目录.
     *
     * @param string $from 源目录
     * @param string $dest 目标目录
     * @param bool $cover 是否覆盖已存在的文件
     */
    public static function copyDir(string $from, string $dest, bool $cover = false): bool
    {
        if (! file_exists($dest) && ! mkdir($dest, 0766, true) && ! is_dir($dest)) {
            return false;
        }

        $dh = @opendir($from);
        while (($fileName = @readdir($dh)) !== false) {
            if (($fileName !== '.') && ($fileName !== '..')) {
                $newFile = "{$dest}/{$fileName}";
                if (! is_dir("{$from}/{$fileName}")) {
                    if (! $cover & file_exists($newFile)) {
                        continue;
                    }
                    if (! copy("{$from}/{$fileName}", $newFile)) {
                        return false;
                    }
                } else {
                    self::copyDir("{$from}/{$fileName}", $newFile, $cover);
                }
            }
        }
        @closedir($dh);

        return true;
    }

    /**
     * 批量改变目录模式(包括子目录和所属文件).
     *
     * @param string $path 路径
     * @param int $filemode 文件模式
     * @param int $dirmode 目录模式
     */
    public static function chmodBatch(string $path, int $filemode = 0766, int $dirmode = 0766): void
    {
        if ($path === '') {
            return;
        }

        if (is_dir($path)) {
            if (! @chmod($path, $dirmode)) {
                return;
            }
            $dh = @opendir($path);
            while (($file = @readdir($dh)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $fullpath = $path . '/' . $file;
                    self::chmodBatch($fullpath, $filemode, $dirmode);
                }
            }
            @closedir($dh);
        } elseif (! is_link($path)) {
            @chmod($path, $filemode);
        }
    }

    /**
     * 删除目录(目录下所有文件,包括本目录).
     */
    public static function delDir(string $path): bool
    {
        if (is_dir($path) && $dh = @opendir($path)) {
            while (($file = @readdir($dh)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $fielpath = $path . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($fielpath)) {
                        self::delDir($fielpath);
                    } else {
                        @unlink($fielpath);
                    }
                }
            }
            @closedir($dh);
            return @rmdir($path);
        }
        return false;
    }

    /**
     * 清空目录(删除目录下所有文件,仅保留当前目录).
     */
    public static function clearDir(string $path): bool
    {
        if (empty($path) || ! is_dir($path)) {
            return false;
        }

        $dirs = [];
        $dir = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $single => $file) {
            $fpath = $file->getRealPath();
            if ($file->isDir()) {
                $dirs[] = $fpath;
            } else {
                //先删除文件
                @unlink($fpath);
            }
        }

        //再删除目录
        rsort($dirs);
        foreach ($dirs as $dir) {
            @rmdir($dir);
        }

        unset($iterator, $dirs);
        return true;
    }

    /**
     * 格式化路径字符串(路径后面加/).
     */
    public static function formatDir(string $dir): string
    {
        if ($dir === '') {
            return '';
        }

        $order = [
            '\\',
            "'",
            '#',
            '=',
            '`',
            '$',
            '%',
            '&',
            ';',
            '|',
        ];
        $replace = [
            '/',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        $dir = str_replace($order, $replace, $dir);
        return rtrim(preg_replace(RegularHelper::$patternDoubleSlash, '/', $dir), ' /　') . '/';
    }
}
