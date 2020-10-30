<?php

declare(strict_types = 1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * Class TestController
 * @package App\Controller
 * @Controller(prefix="test")
 */
class TestOneController extends AbstractController
{

    /**
     * @Inject()
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @RequestMapping(path="index")
     * @param \Hyperf\HttpServer\Contract\RequestInterface  $request
     * @param \Hyperf\HttpServer\Contract\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        dump($this->request);
        dump('asdasdasd22222222');
        return $response->raw('Hello Hyperf!');
    }

    /**
     *
     * @RequestMapping(path="logger")
     */
    public function logger()
    {
        dump($this->logger);
        dump($this->request);
    }
}
