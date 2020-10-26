<?php
declare(strict_types = 1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Class User
 * @package App\Constants
 * @Constants()
 */
class User extends AbstractConstants
{
    const REGISTER = 'user_register';

    const CHANGE_MOBILE = 'change_mobile';

    const FORGET_PASSWORD = 'forget_password';

    const CHANGE_PASSWORD = 'change_password';
}
