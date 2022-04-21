<?php

declare(strict_types=1);

namespace Loner\Stream\Site;

/**
 * 无连接的
 */
interface StatelessInterface
{
    /**
     * 数据包长度上限
     *
     * @var int
     */
    public const MAX_PACKAGE_SIZE = (64 << 10) - 1;
}
