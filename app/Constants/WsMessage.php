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
