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
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method  static getMessage(int $code)
 */
class ErrorCode extends AbstractConstants
{
    //基本错误码 0～1000
    const AUTH_ERROR = 401;

    //用户错误码 3000～3999

    const
        USER_NOT_FOUND = 3001;

    const
        USER_ID_INVALID = 3002;

    const
        USER_EMAIL_ALREADY_USE = 3003;

    const
        USER_PASSWORD_ERROR = 3004;

    const
        USER_CREATE_APPLICATION_FAIL = 3005;

    const
        USER_APPLICATION_SET_READ_FAIL = 3006;

    const
        USER_INFO_MODIFY_FAIL = 3007;

    const
        USER_APPLICATION_NOT_FOUND = 3008;

    const
        USER_APPLICATION_PROCESSED = 3009;

    const
        USER_APPLICATION_TYPE_WRONG = 3010;

    const
        USER_IN_VIDEO_CALL = 3011;

    const FRIEND_GROUP_CREATE_FAIL = 4001;

    const FRIEND_GROUP_NOT_FOUND = 4002;

    const FRIEND_NOT_FOUND = 4003;

    const FRIEND_NOT_ADD_SELF = 4004;

    const FRIEND_RELATION_ALREADY = 4005;

    const FRIEND_CALL_IN_PROGRESS = 4006;

    const GROUP_CREATE_FAIL = 5001;

    const GROUP_NOT_FOUND = 5002;

    const GROUP_RELATION_CREATE_FAIL = 5010;

    const GROUP_RELATION_ALREADY = 5011;

    const GROUP_FULL = 5012;

    const GROUP_NOT_MEMBER = 5013;

    // ext 9000~9999
    const
        JWT_PRIVATE_KEY_EMPTY = 9001;

    const
        JWT_PUBLIC_KEY_EMPTY = 9002;

    const
        JWT_ALG_EMPTY = 9003;

    const
        NO_PERMISSION_PROCESS = 9004;

    const
        CONFIG_NOT_FOUND = 9005;

    const
        FILE_DOES_NOT_EXIST = 9006;

    public static $errorMessages = [
        self::AUTH_ERROR => 'Authorization has been denied for this request !',

        self::USER_NOT_FOUND => 'User not found!',
        self::USER_ID_INVALID => 'The user id is invalid !',
        self::USER_EMAIL_ALREADY_USE => 'This mailbox is already in use !',
        self::USER_PASSWORD_ERROR => 'User password input error !',
        self::USER_CREATE_APPLICATION_FAIL => 'Failed to create user application !',
        self::USER_APPLICATION_SET_READ_FAIL => 'application set to read failed',
        self::USER_INFO_MODIFY_FAIL => 'Failed to modify user information !',
        self::USER_APPLICATION_NOT_FOUND => 'Application information does not exist !',
        self::USER_APPLICATION_PROCESSED => 'Application information has been processed !',
        self::USER_APPLICATION_TYPE_WRONG => 'Wrong application type !',
        self::USER_IN_VIDEO_CALL => '您正在视频通话中！', // You are in a video call

        self::FRIEND_GROUP_CREATE_FAIL => 'Friend group creation failed !',
        self::FRIEND_GROUP_NOT_FOUND => 'Friend group not found !',
        self::FRIEND_NOT_FOUND => 'Friend not found!',
        self::FRIEND_NOT_ADD_SELF => 'You can\'t add yourself as a friend !',
        self::FRIEND_RELATION_ALREADY => 'You\'re already friends !',
        self::FRIEND_CALL_IN_PROGRESS => '对方正在视频通话中', // Video call in progress

        self::GROUP_CREATE_FAIL => 'Group creation failed !',
        self::GROUP_NOT_FOUND => 'Group not found !',
        self::GROUP_RELATION_CREATE_FAIL => 'Group relation creation failed !',
        self::GROUP_RELATION_ALREADY => 'You\'re already a member of the group !',
        self::GROUP_FULL => 'Group full !',
        self::GROUP_NOT_MEMBER => 'You are not a member of this group !',

        self::JWT_PRIVATE_KEY_EMPTY => 'The private key is invalid !',
        self::JWT_PUBLIC_KEY_EMPTY => 'The public key is invalid !',
        self::JWT_ALG_EMPTY => 'The alg is invalid !',
        self::NO_PERMISSION_PROCESS => 'No permission to process !',
        self::CONFIG_NOT_FOUND => 'Configuration not found !',
        self::FILE_DOES_NOT_EXIST => 'File does not exist !',
    ];
}
