<?php

declare(strict_types=1);

namespace Loner\Stream\Server;

use Loner\Stream\{Exception\DecodedException, Site\StatelessInterface};
use Loner\Stream\Stateless\{Event\Accept, ServerEvent};
use Stringable;

/**
 * 无连接状态的流服务端
 */
abstract class StatelessServer extends Server implements StatelessInterface
{
    /**
     * @inheritDoc
     */
    final protected static function flags(): int
    {
        return STREAM_SERVER_BIND;
    }

    /**
     * @inheritDoc
     */
    protected function accept(): void
    {
        $receive = stream_socket_recvfrom($this->socket, static::MAX_PACKAGE_SIZE, 0, $remoteAddress);
        if ($receive && isset($remoteAddress)) {
            if ($this->protocol === null) {
                $this->eventDispatch(Accept::class, $this, $receive, $remoteAddress);
            } else {
                do {
                    $packageSize = $this->protocol->input($receive);
                    if ($packageSize && strlen($receive) >= $packageSize) {
                        $package = substr($receive, 0, $packageSize);
                        $receive = substr($receive, $packageSize);
                        try {
                            $message = $this->protocol->decode($package);
                            $this->eventDispatch(Accept::class, $this, $message, $remoteAddress);
                        } catch (DecodedException) {
                        }
                        continue;
                    }
                    break;
                } while ($receive);
            }
        }
    }

    /**
     * 设置事件响应
     *
     * @param ServerEvent $event
     * @param callable|null $listener
     * @return static
     */
    public function on(ServerEvent $event, ?callable $listener): static
    {
        $this->eventListeners[$event->value] = $listener;
        return $this;
    }

    /**
     * 发送消息到远程
     *
     * @param Stringable|string $data
     * @param string $remoteAddress
     * @return bool
     */
    public function send(Stringable|string $data, string $remoteAddress): bool
    {
        $package = (string)$data;
        return strlen($package) === stream_socket_sendto($this->socket, $package, 0, $remoteAddress);
    }
}
