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
