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
 * Class MemoryTable.
 * @Constants
 */
class Redis extends AbstractConstants
{
    const FD_TO_USER = 'fdToUser';

    const USER_TO_FD = 'userToFd';

    const SUBJECT_USER_TO_FD = 'subjectUserToFd';

    const SUBJECT_FD_TO_USER = 'subjectFdToUser';

    const SUBJECT_TO_USER = 'subjectToUser';

    const USER_TO_SUBJECT = 'userToSubject';
}
