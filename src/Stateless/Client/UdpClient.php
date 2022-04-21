<?php

declare(strict_types=1);

namespace Loner\Stream\Stateless\Client;

use Loner\Stream\{Client\Networked, Client\StatelessClient, Site\Udp};

/**
 * UDP 流客户端
 */
class UdpClient extends StatelessClient
{
    use Networked, Udp;
}
