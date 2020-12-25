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
namespace App\Kernel\WebSocket\Pool;

use App\Kernel\WebSocket\ClientConnection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Frequency;
use Hyperf\Pool\Pool as BPool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class ClientPool extends BPool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * Pool constructor.
     */
    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('websocket_client.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);

        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
    }

    protected function createConnection(): ConnectionInterface
    {
        return new ClientConnection($this->container, $this, $this->config);
    }
}
