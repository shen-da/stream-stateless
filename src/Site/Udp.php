<?php

declare(strict_types=1);

namespace Loner\Stream\Site;

/**
 * UDP 相关
 */
trait Udp
{
    /**
     * @inheritDoc
     */
    public static function transport(): string
    {
        return 'udp';
    }
}
