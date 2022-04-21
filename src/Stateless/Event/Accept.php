<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless\Event;

use Loner\Stream\Server\StatelessServer;
use Stringable;

/**
 * 无连接状态流服务端事件：收到远程消息
 */
class Accept
{
    public function __construct(
        public readonly StatelessServer $server,
        public readonly Stringable|string $message,
        public readonly string $remoteAddress
    )
    {
    }
}
