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
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Class User.
 * @Constants
 */
class User extends AbstractConstants
{
    public const REGISTER = 'user_register';

    public const CHANGE_MOBILE = 'change_mobile';

    public const FORGET_PASSWORD = 'forget_password';

    public const CHANGE_PASSWORD = 'change_password';
}
