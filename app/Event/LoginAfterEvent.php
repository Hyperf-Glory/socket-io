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
namespace App\Event;

class LoginAfterEvent
{
    /**
     * @var int
     */
    public int $uid = 0;

    /**
     * @var string
     */
    public string $ip = '';

    public function __construct(int $uid, string $ip)
    {
        $this->uid = $uid;
        $this->ip  = $ip;
    }
}
