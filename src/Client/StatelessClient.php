<?php

declare(strict_types=1);

namespace Loner\Stream\Client;

use Loner\Stream\{Exception\DecodedException, Site\StatelessInterface};
use Loner\Stream\Stateless\{ClientEvent, Event\Close, Event\Open, Event\Receive};
use Stringable;

/**
 * 无连接状态的流客户端
 */
abstract class StatelessClient extends Client implements StatelessInterface
{
    /**
     * @inheritDoc
     */
    final protected static function flags(): int
    {
        return STREAM_CLIENT_CONNECT;
    }

    /**
     * @inheritDoc
     */
    protected function dispatchClear(): void
    {
        $this->eventDispatch(Close::class, $this);
        $this->eventListeners = [];
    }

    /**
     * @inheritDoc
     */
    protected function listening(): void
    {
        stream_set_blocking($this->socket, false);
        $this->eventDispatch(Open::class, $this);
        $this->resumeReceive();
    }

    /**
     * @inheritDoc
     */
    public function resumeReceive(): void
    {
        if ($this->socket !== null && $this->receiving === false) {
            $this->receiving = true;
            $this->setReadListener(function () {
                $receive = stream_socket_recvfrom($this->socket, static::MAX_PACKAGE_SIZE, 0, $remoteAddress);
                if ($receive && isset($remoteAddress)) {
                    if ($this->protocol === null) {
                        $this->eventDispatch(Receive::class, $this, $receive);
                    } else {
                        try {
                            $message = $this->protocol->decode($receive);
                            $this->eventDispatch(Receive::class, $this, $message);
                        } catch (DecodedException) {
                        }
                    }
                }
            });
        }
    }

    /**
     * 发送数据到远程，若未指定远程地址，则发送到默认流服务端
     *
     * @param Stringable|string $data
     * @param string|null $remoteAddress
     * @return bool
     */
    public function send(Stringable|string $data, string $remoteAddress = null): bool
    {
        if ($this->socket === null) {
            return false;
        }

        $package = (string)$data;
        return $remoteAddress === null
            ? strlen($package) === stream_socket_sendto($this->socket, $package, 0)
            : strlen($package) === stream_socket_sendto($this->socket, $package, 0, $remoteAddress);
    }

    /**
     * 关闭通信网络，释放资源；若指定发送数据，会在之前进行发送操作
     *
     * @param Stringable|string|null $sendData
     * @param string|null $remoteAddress
     * @return void
     */
    public function close(Stringable|string $sendData = null, string $remoteAddress = null): void
    {
        if ($this->socket === null) {
            return;
        }

        if ($sendData !== null) {
            $this->send($sendData, $remoteAddress);
        }

        $this->receiving = false;
        $this->closeAll();
    }

    /**
     * 设置事件响应
     *
     * @param ClientEvent $event
     * @param callable|null $listener
     * @return static
     */
    public function on(ClientEvent $event, ?callable $listener): static
    {
        $this->eventListeners[$event->value] = $listener;
        return $this;
    }
}
