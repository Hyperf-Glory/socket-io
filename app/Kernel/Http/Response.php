<?php

declare(strict_types = 1);
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
    protected ContainerInterface $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response  = $container->get(ResponseInterface::class);
    }

    public function success(string $message = '', $data = []) : PsrResponseInterface
    {
        return $this->response->json([
            'code' => 200,
            'data' => $data,
            'msg'  => $message,
        ]);
    }

    public function fail($code, $message = '') : PsrResponseInterface
    {
        return $this->response->json([
            'code' => $code,
            'msg'  => $message,
        ]);
    }

    public function parameterError($message = '请求参数错误') : PsrResponseInterface
    {
        return $this->response->json([
            'code' => 301,
            'msg'  => $message,
        ]);
    }

    public function error(string $msg, $data = []) : PsrResponseInterface
    {
        return $this->response->json([
            'code' => 305,
            'msg'  => $msg,
            'data' => $data,
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
                    ->withAddedHeader('Server', 'SocketIO')
                    ->withStatus($throwable->getStatusCode())
                    ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response() : PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }

    public function toWechatXML(string $xml, int $statusCode = 200) : PsrResponseInterface
    {
        return $this->response()
                    ->withStatus($statusCode)
                    ->withAddedHeader('content-type', 'application/xml; charset=utf-8')
                    ->withBody(new SwooleStream($xml));
    }

    public function download(string $file, string $name = '') : PsrResponseInterface
    {
        return $this->container->get(ResponseInterface::class)->download($file, $name);
    }
}
