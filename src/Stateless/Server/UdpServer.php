<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless\Server;

use Loner\Stream\{Server\Networked, Server\StatelessServer, Site\Udp};

/**
 * UDP 流服务端
 */
class UdpServer extends StatelessServer
{
    use Networked, Udp;
}
