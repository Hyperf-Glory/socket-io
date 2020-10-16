<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
        $this->response  = $container->get(ResponseInterface::class);
    }

    public function success($data = []) : PsrResponseInterface
    {
        return $this->response->json([
            'code' => 0,
            'data' => $data,
        ]);
    }

    public function fail($code, $message = '') : PsrResponseInterface
    {
        return $this->response->json([
            'code'    => $code,
            'message' => $message,
        ]);
    }

    public function redirect($url, $status = 302) : PsrResponseInterface
    {
        return $this->response()
                    ->withAddedHeader('Location', (string)$url)
                    ->withStatus($status);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    public function handleException(HttpException $throwable) : PsrResponseInterface
    {
        return $this->response()
                    ->withAddedHeader('Server', 'Hyperf')
                    ->withStatus($throwable->getStatusCode())
                    ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response() : PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }

    /**
     * @param string $xml
     * @param int    $statusCode
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toWechatXML(string $xml, int $statusCode = 200) : PsrResponseInterface
    {
        return $this->response()
                    ->withStatus($statusCode)
                    ->withAddedHeader('content-type', 'application/xml; charset=utf-8')
                    ->withBody(new SwooleStream($xml));
    }
}
