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
 * Class MemoryTable.
 * @Constants
 */
class Redis extends AbstractConstants
{
    public const FD_TO_USER = 'fdToUser';

    public const USER_TO_FD = 'userToFd';

    public const SUBJECT_USER_TO_FD = 'subjectUserToFd';

    public const SUBJECT_FD_TO_USER = 'subjectFdToUser';

    public const SUBJECT_TO_USER = 'subjectToUser';

    public const USER_TO_SUBJECT = 'userToSubject';
}
