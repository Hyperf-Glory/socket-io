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
namespace App\Kernel\Http;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    public function success(string $message = '', $data = []): PsrResponseInterface
    {
        return $this->response->json([
            'code' => 200,
            'data' => $data,
            'msg' => $message,
        ]);
    }

    public function fail($code, $message = ''): PsrResponseInterface
    {
        return $this->response->json([
            'code' => $code,
            'msg' => $message,
        ]);
    }

    public function parmasError($message = '请求参数错误'): PsrResponseInterface
    {
        return $this->response->json([
            'code' => 301,
            'msg' => $message,
        ]);
    }

    public function error(string $msg, $data = []): PsrResponseInterface
    {
        return $this->response->json([
            'code' => 305,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function redirect($url, $status = 302): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Location', (string) $url)
            ->withStatus($status);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Server', 'Hyperf')
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }

    public function toWechatXML(string $xml, int $statusCode = 200): PsrResponseInterface
    {
        return $this->response()
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/xml; charset=utf-8')
            ->withBody(new SwooleStream($xml));
    }
}
