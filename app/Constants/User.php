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
 * Class User.
 * @Constants
 */
class User extends AbstractConstants
{
    const REGISTER = 'user_register';

    const CHANGE_MOBILE = 'change_mobile';

    const FORGET_PASSWORD = 'forget_password';

    const CHANGE_PASSWORD = 'change_password';
}
