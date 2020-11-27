<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Component\Mail;
use App\JsonRpc\Contract\InterfaceUserService;
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

    /**
     * @RequestMapping(path="rpc")
     */
    public function rpc()
    {
        $user = $this->container->get(InterfaceUserService::class);
        $ret = $user->get(1);
        return $ret;
//        dump($ret);
    }


    /**
     * @RequestMapping(path="mail")
     */
    public function mail(){
        $mail = di(Mail::class);
        dump($mail ->send(Mail::CHANGE_EMAIL, '绑定邮箱', '213213@qq.com'));
    }
}
