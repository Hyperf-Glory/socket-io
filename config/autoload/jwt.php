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
return [
    'login_type' => env('JWT_LOGIN_TYPE', 'sso'), //  登录方式，sso为单点登录，mpop为多点登录

    /*
     * 单点登录自定义数据中必须存在uid的键值，这个key你可以自行定义，只要自定义数据中存在该键即可
     */
    'sso_key' => 'uid',

    'secret' => env('JWT_SECRET', 'phper666'), // 非对称加密使用字符串,请使用自己加密的字符串

    /*
     * JWT 权限keys
     * 对称算法: HS256, HS384 & HS512 使用 `JWT_SECRET`.
     * 非对称算法: RS256, RS384 & RS512 / ES256, ES384 & ES512 使用下面的公钥私钥.
     */
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'), // 公钥，例如：'file:///path/to/public/key'
        'private' => env('JWT_PRIVATE_KEY'), // 私钥，例如：'file:///path/to/private/key'
    ],

    'ttl' => env('JWT_TTL', 7200), // token过期时间，单位为秒

    'alg' => env('JWT_ALG', 'HS256'), // jwt的hearder加密算法

    /*
     * 支持的算法
     */
    'supported_algs' => [
        'HS256' => 'Lcobucci\JWT\Signer\Hmac\Sha256',
        'HS384' => 'Lcobucci\JWT\Signer\Hmac\Sha384',
        'HS512' => 'Lcobucci\JWT\Signer\Hmac\Sha512',
        'ES256' => 'Lcobucci\JWT\Signer\Ecdsa\Sha256',
        'ES384' => 'Lcobucci\JWT\Signer\Ecdsa\Sha384',
        'ES512' => 'Lcobucci\JWT\Signer\Ecdsa\Sha512',
        'RS256' => 'Lcobucci\JWT\Signer\Rsa\Sha256',
        'RS384' => 'Lcobucci\JWT\Signer\Rsa\Sha384',
        'RS512' => 'Lcobucci\JWT\Signer\Rsa\Sha512',
    ],

    /*
     * 对称算法名称
     */
    'symmetry_algs' => [
        'HS256',
        'HS384',
        'HS512',
    ],

    /*
     * 非对称算法名称
     */
    'asymmetric_algs' => [
        'RS256',
        'RS384',
        'RS512',
        'ES256',
        'ES384',
        'ES512',
    ],

    /*
     * 是否开启黑名单，单点登录和多点登录的注销、刷新使原token失效，必须要开启黑名单，目前黑名单缓存只支持hyperf缓存驱动
     */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
     * 黑名单的宽限时间 单位为：秒，注意：如果使用单点登录，该宽限时间无效
     */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
     * 黑名单缓存token时间，注意：该时间一定要设置比token过期时间要大一点，默认为1天,最好设置跟过期时间一样
     */
    'blacklist_cache_ttl' => env('JWT_TTL', 86400),

    'blacklist_prefix' => 'cloud_jwt', // 黑名单缓存的前缀

    'scene' => [
        'default' => [],
        'cloud' => [
            'secret' => 'cloud', // 非对称加密使用字符串,请使用自己加密的字符串
            'login_type' => 'sso', //  登录方式，sso为单点登录，mpop为多点登录
            'sso_key' => 'cloud_uid',
            'ttl' => 7200, // token过期时间，单位为秒
            'blacklist_cache_ttl' => env('JWT_TTL', 7200), // 黑名单缓存token时间，注意：该时间一定要设置比token过期时间要大一点，默认为100秒,最好设置跟过期时间一样
        ],
    ],
];
