<?php
declare(strict_types = 1);

namespace App\Controller;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

/**
 * @Controller(prefix="/test")
 * Class TestController
 * @package App\Controller
 */
class TestController extends AbstractController
{
    /**
     * @Inject()
     * @var \Phper666\JWTAuth\JWT
     */
    protected $jwt;

    /**
     * @RequestMapping(path="token")
     */
    public function token()
    {
        $username = $this->request->input('username', 'user');
        $password = $this->request->input('password', 'pass');
        if ($username && $password) {
            $userData = [
                'cloud_uid' => 1, // 如果使用单点登录，必须存在配置文件中的sso_key的值，一般设置为用户的id
                'username'  => 'xx',
            ];
            // 使用默认场景登录
            $token = $this->jwt->setScene('cloud')->getToken($userData);
            $data  = [
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'token' => $token,
                    'exp'   => $this->jwt->getTTL(),
                ]
            ];
            return $this->response->success($data);
        }
        return null;
    }

    /**
     * @RequestMapping(path="check-token")
     */
    public function checkToken()
    {
        $token = $this->request->input('token');
        var_dump($this->jwt->checkToken($token));
        var_dump($this->jwt->getParserData($token));
    }

    /**
     * @RequestMapping(path="rate-limit")
     * @RateLimit(create=1,consume=2,capacity=2,waitTimeout=3,limitCallback={TestController::class,"limitCallback"})
     */
    public function rateLimit()
    {
        sleep(1);
        return ["QPS 2, 峰值2"];
    }


    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        var_dump($seconds);
        // $seconds 下次生成Token 的间隔, 单位为秒
        // $proceedingJoinPoint 此次请求执行的切入点
        // 可以通过调用 `$proceedingJoinPoint->process()` 继续执行或者自行处理
        return $proceedingJoinPoint->process();
    }
}


