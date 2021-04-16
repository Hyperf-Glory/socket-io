<?php

declare(strict_types = 1);
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
    public const WS_MESSAGE_CMD_EVENT = 'system.event';

    public const WS_MESSAGE_CMD_ERROR = 'system.error';

    public const EVENT_USER_STATUS = 'setUserStatus';

    public const EVENT_GET_MESSAGE = 'getMessage';

    public const EVENT_GET_UNREAD_APPLICATION_COUNT = 'getUnreadApplicationCount';

    public const EVENT_FRIEND_AGREE_APPLY = 'friendAgreeApply';

    public const EVENT_GROUP_AGREE_APPLY = 'groupAgreeApply';

    public const EVENT_FRIEND_VIDEO_ROOM = 'friendVideoRoom';

    public const EVENT_CHAT_MESSAGE = 'chat_message';

    public const EVENT_REVOKE_RECORDS = 'revoke_records';
}
