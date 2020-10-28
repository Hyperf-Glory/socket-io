<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\BindingDependency;
use App\Component\MessageParser;
use App\Service\GroupService;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\ModelCache\Redis\HashGetMultiple;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Redis\Lua\Hash\HGetAllMultiple;
use Hyperf\Redis\RedisFactory;

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

    /**
     * @RequestMapping(path="group")
     */
    public function group()
    {
        //TODO 1.根据groupid获取uid
        $groupUids = make(GroupService::class)->getGroupUid(1);
        $groupUids = array_column($groupUids, 'user_id');
        $groupUids = [
            1,
            2,
            5,
            6
        ];
        $ips       = [
            '127.0.0.1',
        ];
        var_dump(array_rand($ips));
        //TODO 2.根据ip获取uid
        $ipuids = BindingDependency::getIpUid('127.0.0.1');
        $ipUids = array_intersect($groupUids, $ipuids);
        //TODO 3.取出uid对应的fd
        $fds = BindingDependency::fds($ipUids);
        var_dump($fds);
    }

    /**
     * @RequestMapping(path="json")
     */
    public function json()
    {
        $data      = [
            'hello' => 'word',
            'word'  => 'hello'
        ];
        $startTime = microtime(true);
        $json      = json_encode($data);
        dump(json_decode($json, true));
        $endTime = microtime(true);
        echo 'php_json执行了' . ($endTime - $startTime) * 1000 . ' ms' . PHP_EOL;
    }

    /**
     * @RequestMapping(path="swoolejson")
     */
    public function swoolejson(){
        $data1      = [
            'hello' => 'word1',
            'word'  => 'hello1'
        ];
        $startTime1 = microtime(true);
        $json1      = json_encode($data1);
        dump(MessageParser::decode($json1));
        $endTime1 = microtime(true);
        echo 'swoole_json执行了' . ($endTime1 - $startTime1) * 1000 . ' ms' . PHP_EOL;
    }

}


