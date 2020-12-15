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
use App\Helper\StringHelper;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param null|string $id
     *
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     *
     * @param \Throwable $throwable
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}
if (! function_exists('verifyIp')) {
    function verifyIp($realip)
    {
        return filter_var($realip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}
if (! function_exists('getClientIp')) {
    function getClientIp()
    {
        try {
            /**
             * @var ServerRequestInterface $request
             */
            $request = Context::get(ServerRequestInterface::class);
            $ip_addr = $request->getHeaderLine('x-forwarded-for');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('remote-host');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('x-real-ip');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
        } catch (Throwable $e) {
            return '0.0.0.0';
        }
        return '0.0.0.0';
    }
}

/*
 * 替换文本中的url 为 a标签
 *
 * @param string $str
 *
 * @return null|string|string[]
 */
if (! function_exists('replace_url_link')) {
    function replace_url_link(string $str)
    {
        $re = '@((https|http)?://([-\w\.]+)+(:\d+)?(/([\w/_\-.#%]*(\?\S+)?)?)?)@';
        return preg_replace_callback($re, static function ($matches) {
            return sprintf('<a href="%s" target="_blank">%s</a>', trim($matches[0], '&quot;'), $matches[0]);
        }, $str);
    }
}

/**
 * 随机生成图片名.
 *
 * @param string $ext 图片后缀名
 * @param int $width 图片宽度
 * @param int $height 图片高度
 */
function create_image_name(string $ext, int $width, int $height): string
{
    return uniqid('', false) . StringHelper::randString(18) . uniqid('', false) . '_' . $width . 'x' . $height . '.' . $ext;
}

/**
 * 从HTML文本中提取所有图片.
 *
 * @param $content
 */
function get_html_images($content): array
{
    $pattern = "/<img.*?src=[\\'|\"](.*?)[\\'|\"].*?[\\/]?>/";
    preg_match_all($pattern, htmlspecialchars_decode($content), $match);
    $data = [];
    if (! empty($match[1])) {
        foreach ($match[1] as $img) {
            if (! empty($img)) {
                $data[] = $img;
            }
        }
        return $data;
    }

    return $data;
}

/**
 * 生成6位字符的短码字符串.
 */
function create_short_code(string $string): string
{
    $result = sprintf('%u', crc32($string));
    $show = '';
    while ($result > 0) {
        $s = $result % 62;
        if ($s > 35) {
            $s = chr($s + 61);
        } elseif ($s > 9 && $s <= 35) {
            $s = chr($s + 55);
        }
        $show .= $s;
        $result = floor($result / 62);
    }

    return $show;
}

/**
 * 获取媒体文件url.
 *
 * @param string $path 文件相对路径
 */
function get_media_url(string $path): string
{
    return config('image_url', '') . '/' . $path;
}
