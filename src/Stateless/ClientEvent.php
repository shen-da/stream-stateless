<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless;

use Loner\Stream\Event\OpenFail;
use Loner\Stream\Stateless\Event\{Close, Open, Receive};

/**
 * 无连接状态的流客户端事件
 */
enum ClientEvent: string
{
    case OpenFail = OpenFail::class;
    case Open = Open::class;
    case Receive = Receive::class;
    case Close = Close::class;
}
