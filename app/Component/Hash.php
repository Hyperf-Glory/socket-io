<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
