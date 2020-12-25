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
namespace App\JsonRpc\Contract;

/**
 * @deprecated
 * Interface InterfaceProxyService
 */
interface InterfaceProxyService
{
    /**
     *@deprecated
     *
     * @return mixed
     */
    public function pushMessage(int $uid, string $message);

    /**
     * @deprecated
     *
     * @return mixed
     */
    public function broadcast(string $message);

    /**
     * @deprecated
     *
     * @return mixed
     */
    public function group(int $groupId, string $message);
}
