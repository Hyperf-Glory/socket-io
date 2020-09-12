<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param null|string $id
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
if (!function_exists('verifyIp')) {
    function verifyIp($realip) {
        return filter_var($realip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}
if (!function_exists('getClientIp')) {
    function getClientIp() {
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

