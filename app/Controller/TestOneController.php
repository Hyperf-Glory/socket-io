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

use App\Component\Mail;
use App\JsonRpc\Contract\InterfaceUserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * Class TestController.
 * @Controller(prefix="test")
 */
class TestOneController extends AbstractController
{
    /**
     * @Inject
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @RequestMapping(path="index")
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
        return $user->get(1);
//        dump($ret);
    }

    /**
     * @RequestMapping(path="mail")
     */
    public function mail()
    {
        $mail = di(Mail::class);
        dump($mail->send(Mail::CHANGE_EMAIL, '绑定邮箱', '213213@qq.com'));
    }
}
