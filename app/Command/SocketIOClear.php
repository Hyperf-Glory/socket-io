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
namespace App\Command;

use Hyperf\Command\Command;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Symfony\Component\Console\Input\InputArgument;

class SocketIOClear extends Command
{
    protected string $redisPrefix = 'ws';

    protected string $connection = 'default';

    /**
     * @var \Redis|Redis
     */
    private $redis;

    public function __construct(RedisFactory $factory)
    {
        parent::__construct('socketio-self:clear');
        $this->redis = $factory->get(env('CLOUD_REDIS', 'default'));
    }

    public function handle(): void
    {
        $nsp = $this->input->getArgument('namespace') ?? '/';
        $serverId = $this->input->getArgument('serverId');
        $prefix = implode(':', [
            $this->redisPrefix,
            $nsp,
            'fds',
            $serverId,
        ]);
        $iterator = null;
        while (false !== ($keys = $this->redis->scan($iterator, "{$prefix}*"))) {
            $this->redis->del($keys);
        }
    }

    protected function getArguments(): array
    {
        return [
            ['namespace', InputArgument::REQUIRED, 'The namespace to be cleaned up.'],
            ['serverId', InputArgument::REQUIRED, 'The  self SocketIO Server to be cleaned up.'],
        ];
    }
}
