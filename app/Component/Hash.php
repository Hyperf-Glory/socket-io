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
namespace App\Component;

use Crypto\HashException;

class Hash
{
    /**
     * Make a hash from the given plain data.
     */
    public static function make(string $plain): string
    {
        $result = password_hash($plain, PASSWORD_BCRYPT);
        if ($result === false) {
            throw new HashException();
        }

        return $result;
    }

    /**
     * Verify the given plain with the given hashed value.
     */
    public static function verify(string $plain, string $hashed): bool
    {
        return password_verify($plain, $hashed);
    }
}
