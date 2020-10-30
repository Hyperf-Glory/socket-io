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
 * Class WsMessage.
 * @Constants
 */
class WsMessage extends AbstractConstants
{
    const WS_MESSAGE_CMD_EVENT = 'system.event';

    const WS_MESSAGE_CMD_ERROR = 'system.error';

    const EVENT_USER_STATUS = 'setUserStatus';

    const EVENT_GET_MESSAGE = 'getMessage';

    const EVENT_GET_UNREAD_APPLICATION_COUNT = 'getUnreadApplicationCount';

    const EVENT_FRIEND_AGREE_APPLY = 'friendAgreeApply';

    const EVENT_GROUP_AGREE_APPLY = 'groupAgreeApply';

    const EVENT_FRIEND_VIDEO_ROOM = 'friendVideoRoom';
}
