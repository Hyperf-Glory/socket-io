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
