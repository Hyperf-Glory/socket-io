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
namespace App\Listener;

use App\Component\Command\CliTable;
use App\Component\Command\CliTableManipulator;
use App\Component\Command\Server;
use Carbon\Carbon;
use Codedungeon\PHPCliColors\Color;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;

class OnStartListener extends AbstractProcessListener implements ListenerInterface
{
    public function listen() : array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event) : void
    {
        echo Color::GREEN, sprintf('[%s]', Carbon::now()->toDateTimeString()), ' ', Color::CYAN,
        PHP_EOL,
        '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',
        PHP_EOL,
        Server::LOGO,
        PHP_EOL,
        '<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<',
        PHP_EOL,
        Color::YELLOW,
        '
        _______  _        _______  _______  _______  _______ 
|\     /|(  ____ \( \      (  ____ \(  ___  )(       )(  ____ \
| )   ( || (    \/| (      | (    \/| (   ) || () () || (    \/
| | _ | || (__    | |      | |      | |   | || || || || (__    
| |( )| ||  __)   | |      | |      | |   | || |(_)| ||  __)   
| || || || (      | |      | |      | |   | || |   | || (      
| () () || (____/\| (____/\| (____/\| (___) || )   ( || (____/\
(_______)(_______/(_______/(_______/(_______)|/     \|(_______/
            ', PHP_EOL,
        Color::RESET, PHP_EOL,
        '----------------------------------------------------------------------', PHP_EOL;
        echo Color::YELLOW, '| 基于Hyperf2.1微服务协程框架开发的Socket-IO分布式IM系统 |', PHP_EOL,
        '----------------------------------------------------------------------', PHP_EOL;
        $data  = [
            [
                'php-version'    => PHP_VERSION,
                'swoole-version' => SWOOLE_VERSION,
                'app-name'       => env('APP_NAME'),
                'date-time'      => Carbon::now()->timestamp,
                'os'             => PHP_OS
            ],
        ];
        $table = new CliTable();
        $table->setTableColor('blue');
        $table->setHeaderColor('cyan');
        $table->addField('PHP-VERSION', 'php-version', false, 'white');
        $table->addField('SWOOLE-VERSION', 'swoole-version', false, 'white');
        $table->addField('APP-NAME', 'app-name', false, 'read');
        $table->addField('Date-Time', 'date-time', new CliTableManipulator('nicetime'), 'red');
        $table->injectData($data);
        $table->display();
    }
}
