<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless;

use Loner\Stream\Event\{Start, Stop};
use Loner\Stream\Stateless\Event\Accept;

/**
 * 无连接状态的流服务端事件
 */
enum ServerEvent: string
{
    case Start = Start::class;
    case Stop = Stop::class;
    case Accept = Accept::class;
}
