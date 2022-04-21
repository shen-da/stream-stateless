<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless\Event;

use Loner\Stream\Client\StatelessClient;

/**
 * 无连接状态的流客户端事件：通信网络已关闭
 */
class Close
{
    public function __construct(public readonly StatelessClient $client)
    {
    }
}
