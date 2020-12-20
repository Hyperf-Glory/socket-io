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
namespace App\Listener;

use App\Component\Command\Server;
use Carbon\Carbon;
use Codedungeon\PHPCliColors\Color;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\ApplicationContext;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class OnShutdownListener implements ListenerInterface
{
    protected $redisPrefix = 'ws';

    public function listen(): array
    {
        return [
            OnShutdown::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof OnShutdown) {
            echo Color::GREEN, sprintf('[%s]', Carbon::now()->toDateTimeString()), ' ', Color::CYAN,
            PHP_EOL,
            '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',
            PHP_EOL,
            Server::LOGO,
            PHP_EOL,
            '<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<',PHP_EOL;
            echo '          
 ______            _______    ______            _______ 
(  ___ \ |\     /|(  ____ \  (  ___ \ |\     /|(  ____ \
| (   ) )( \   / )| (    \/  | (   ) )( \   / )| (    \/
| (__/ /  \ (_) / | (__      | (__/ /  \ (_) / | (__    
|  __ (    \   /  |  __)     |  __ (    \   /  |  __)   
| (  \ \    ) (   | (        | (  \ \    ) (   | (      
| )___) )   | |   | (____/\  | )___) )   | |   | (____/\
|/ \___/    \_/   (_______/  |/ \___/    \_/   (_______/

',PHP_EOL;
            $this->socketIoClearCommand();
            echo Color::GREEN,'Clean Up Success!';
        }
    }

    /**
     * 清除全部socket-io.
     * @throws \Exception
     */
    private function socketIoClearCommand(): void
    {
        $command = 'socketio-self:clear';

        $params = ['command' => $command, 'namespace' => '/', 'serverId' => SocketIO::$serverId];

        // 可以根据自己的需求, 选择使用的 input/output
        $input = new ArrayInput($params);
        $output = new NullOutput();

        $container = ApplicationContext::getContainer();

        /** @var \Symfony\Component\Console\Application $application */
        $application = $container->get(ApplicationInterface::class);
        $application->setAutoExit(false);

        try {
            $exitCode = $application->find($command)->run($input, $output);
        } catch (\Throwable $throwable) {
            echo Color::CYAN,$throwable->getMessage();
        }
    }
}
