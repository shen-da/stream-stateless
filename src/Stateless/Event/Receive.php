<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless\Event;

use Loner\Stream\Client\StatelessClient;
use Stringable;

/**
 * 无连接状态的流客户端事件：收到流服务端消息
 */
class Receive
{
    public function __construct(public readonly StatelessClient $client, public readonly Stringable|string $message)
    {
    }
}
