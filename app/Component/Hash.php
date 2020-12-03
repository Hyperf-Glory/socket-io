<?php
declare(strict_types = 1);

namespace App\Component;

use Crypto\HashException;

class Hash
{
    /**
     * Make a hash from the given plain data
     *
     * @param string $plain
     *
     * @return string
     */
    public static function make(string $plain) : string
    {
        $result = password_hash($plain, PASSWORD_BCRYPT);
        if ($result === false) {
            throw new HashException();
        }

        return $result;
    }

    /**
     * Verify the given plain with the given hashed value
     *
     * @param string $plain
     * @param string $hashed
     *
     * @return bool
     */
    public static function verify(string $plain, string $hashed) : bool
    {
        return password_verify($plain, $hashed);
    }
}
