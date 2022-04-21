## 无连接状态的流服务组件

用于构建无连接状态（基于 UDP 协议）的流服务端及客户端

### 运行依赖

- php: ^8.1
- [loner/stream][1]: ^1.0

### 安装

```
composer require loner/stream-stateless
```

### 快速入门

* UDP 服务端

  ```php
  #!/usr/bin/env php
  <?php
  
  declare(strict_types=1);
  
  use Loner\Reactor\Builder;
  use Loner\Stream\Event\{Start, Stop};
  use Loner\Stream\Stateless\{Event\Accept, Server\UdpServer, ServerEvent};
  
  // composer 自加载
  require_once __DIR__ . '/../vendor/autoload.php';
  
  // 事件轮询反应器
  $reactor = Builder::create();
  
  // 创建服务端：事件轮询反应器、主机名、端口号、绑定上下文配置（默认空数组）、是否端口复用（默认 Linux >= 3.9）
  $server = new UdpServer($reactor, '0.0.0.0', 6957);
  
  // 绑定事件响应
  $server
      // 服务器启动（创建监听网络后、进入事件循环前）
      ->on(ServerEvent::Start, function (Start $event) {
          echo sprintf('Start: %s', $event->server->getSocketAddress()), PHP_EOL;
      })
      // 服务器停止（移除监听网络后、破坏事件循环前）
      ->on(ServerEvent::Stop, function (Stop $event) {
          echo sprintf('Stop: %s', $event->server->getSocketAddress()), PHP_EOL;
      })
      // 收到远程消息
      ->on(ServerEvent::Accept, function (Accept $event) {
          echo sprintf('Accept: %s => %s', $event->remoteAddress, $event->message), PHP_EOL;
          $event->server->send('Hi. I\'m the server.', $event->remoteAddress);
      });
  
  // 启动服务器
  $server->start();
  ```

* UDP 客户端

  ```php
  #!/usr/bin/env php
  <?php
  
  declare(strict_types=1);
  
  use Loner\Reactor\Builder;
  use Loner\Stream\Event\OpenFail;
  use Loner\Stream\Stateless\{Client\UdpClient, ClientEvent, Event\Close, Event\Open, Event\Receive};
  
  // composer 自加载
  require_once __DIR__ . '/../vendor/autoload.php';
  
  // 事件轮询反应器
  $reactor = Builder::create();
  
  // 创建客户端：事件轮询反应器、主机名、端口号、绑定上下文配置（默认空数组）
  $client = new UdpClient($reactor, '127.0.0.1', 6957);
  
  // 绑定事件响应
  $client
      // 开启通信网络失败
      ->on(ClientEvent::OpenFail, function (OpenFail $event) {
          echo sprintf('OpenFail：%s => [%d] [%s]', $event->client->getSocketAddress(), $event->code, $event->message), PHP_EOL;
      })
      // 通信网络已开启
      ->on(ClientEvent::Open, function (Open $event) {
          $client = $event->client;
          echo sprintf('Open: %s', $client->getSocketAddress()), PHP_EOL;
          $client->send('Hi. I\'m a client.');
          $client->send('Hi. I\'m a client.');
          $client->send('Hi. I\'m a client.');
          $client->send('Hi. I\'m a client.');
          $client->send('Hi. I\'m a client.');
      })
      // 收到服务端消息
      ->on(ClientEvent::Receive, function (Receive $event) {
          $client = $event->client;
          echo sprintf('Receive: %s', $event->message), PHP_EOL;
          static $requestTimes = 0;
          if (++$requestTimes === 5) {
              $client->close();
          }
      })
      // 通信网络已关闭
      ->on(ClientEvent::Close, function (Close $event) {
          echo sprintf('Close: %s', $event->client->getSocketAddress()), PHP_EOL;
      });
  
  // 监听网络
  $client->listen();
  
  // 进入事件轮询
  $reactor->loop();
  ```

### 组件功能说明

继承【流服务基础组件】功能，详见【 [loner/stream][1] 】

补充说明：

* UDP 服务端：Loner\Stream\Stateless\Server\UdpServer

    ```php
    use Loner\Stream\Event\{Start, Stop};
    use Loner\Stream\Stateless\{Event\Accept, Server\UdpServer, ServerEvent};
    
    /** @var UdpServer $server */
    
    // 监听主机地址
    $host = $server->getHost();
    // 监听端口号
    $port = $server->getPort();
    
    // 是否端口复用
    $reusable = $server->reusable();
    // 绑定上下文端口复用设置
    $server->reusePort();
    
    // 绑定事件响应
    // $server->on(ServerEvent $event, ?callable $listener): static;
    $server
        // 服务器启动（创建监听网络后、进入事件循环前）
        ->on(ServerEvent::Start, function (Start $event): void {
            // 当前服务端
            $server = $event->server;
    
            // 业务代码
        })
        // 服务器停止（移除监听网络后、破坏事件循环之前）
        ->on(ServerEvent::Stop, function (Stop $event): void {
            // 当前服务端
            $server = $event->server;
    
            // 业务代码
        })
        // 收到远程消息
        ->on(ServerEvent::Accept, function (Accept $event): void {
            // 当前服务端
            $server = $event->server;
            /** @var Stringable|string $message 有应用层协议时，为消息实体；否则为接收数据字符串 */
            $message = $event->message;
            // 消息来源地址
            $remoteAddress = $event->remoteAddress;
    
            // 业务代码
        });
    
    /** @var string $remoteAddress */
    /** @var string $package */
    /** @var Stringable $message */
    
    // 发送消息到指定地址
    // 1. 常规方式
    $server->send($package, $remoteAddress);
    // 2. 存在应用层协议
    $server->send($message, $remoteAddress);
    $server->send((string)$message, $remoteAddress);
    ```

* UDP 客户端：Loner\Stream\Stateless\Client\UdpClient

    ```php
    use Loner\Stream\Event\OpenFail;
    use Loner\Stream\Stateless\Event\{Close, Open, Receive};
    use Loner\Stream\Stateless\{Client\UdpClient, ClientEvent};
    
    /** @var UdpClient $client */
    
    // 获取监听主机地址
    $host = $client->getHost();
    // 获取监听端口号
    $port = $client->getPort();
    
    // 返回本地地址
    $client->getLocalAddress();
    // 返回远程（服务端）地址
    $client->getRemoteAddress();
    
    // 绑定事件响应
    // $client->on(ServerEvent $event, ?callable $listener): static;
    $client
        // 开启通信网络失败
        ->on(ClientEvent::OpenFail, function (OpenFail $event): void {
            // 当前客户端
            $client = $event->client;
            // 错误信息
            $message = $event->message;
            // 错误码
            $code = $event->code;
    
            // 业务代码
        })
        // 通信网络已开启
        ->on(ClientEvent::Open, function (Open $event): void {
            // 当前客户端
            $client = $event->client;
    
            // 业务代码
        })
        // 收到服务端消息
        ->on(ClientEvent::Receive, function (Receive $event): void {
            // 当前客户端
            $client = $event->client;
            /** @var Stringable|string $message 有应用层协议时，为消息实体；否则为接收数据字符串 */
            $message = $event->message;
    
            // 业务代码
        })
        // 通信网络已关闭
        ->on(ClientEvent::Close, function (Close $event): void {
            // 当前客户端
            $client = $event->client;
    
            // 业务代码
        });
    
    /** @var string $package */
    /** @var Stringable $message */
    /** @var string $remoteAddress */
    
    // 发送消息到服务端
    // 1. 常规方式
    $client->send($package);
    // 2. 存在应用层协议
    $client->send($message);
    $client->send((string)$message);
    
    // 发送消息到指定地址（当指定第二个参数时）
    $client->send($package, $remoteAddress);
    $client->send($message, $remoteAddress);
    $client->send((string)$message, $remoteAddress);
    
    // 关闭客户端
    // 1. 直接关闭
    $client->close();
    // 2. 发送消息到服务端后关闭
    $client->close($package);
    $client->close($message);
    $client->close((string)$message);
    // 3. 发送消息到指定地址后关闭
    $client->close($package, $remoteAddress);
    $client->close($message, $remoteAddress);
    $client->close((string)$message, $remoteAddress);
    ```

[1]:https://github.com/shen-da/stream