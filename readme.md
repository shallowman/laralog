# Laralog 

## 功能

- 提供 Laravel Http 中间件，捕捉 Http 请求生命周期内的必要信息
- Http 生命周期信息以 `JSON` 格式记录到日志文件中
- 提供了日志 `JSON` 化格式组件，可以无缝使用 `Filebeat` 采集日志到 `ES` 中，避免做格式优化
- 提供了过滤请求中敏感信息的配置，可以过滤到请求参数中的敏感信息，例如，密码等
## 安装 
1. 方式一  
    - 添加 `shallowman/laralog` 包声明到工程项目的 `composer.json` 文件中
    - 使用 `composer install` 命令安装
    
2. 方式二  
    - 使用如下命令直接安装依赖
        ```sh
        $ composer require shallowman/laralog
        ```
3. 配置文件发布
    - 在项目目录下面运行如下命令，发布 `config` 资源文件
        ```sh
        php artisan vendor:publish --provider="Shallowman\Laralog\ServiceProvider"
        ```
## 配置
### 配置 `Laravel` `Http` 中间件

- 在 `app\Http\Kernel.php` 文件中，找到 `protected $middleware` 属性，添加如下声明。
    ```php
    $middleware = [
        ...
        \Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle::class,
    ];
    ```

### 配置写日志组件
- 在 `.env` 配置中设置默认 `LOG_CHANNEL` 

    ```dotenv
    LOG_CHANNEL=daily
    ```
    
- 在 `config/logging.php` 中，设置默认日志频道 `channel` 为 `daily` 的日志组件，添加如下配置声明 

    ```php
    'daily' => [
         // Monolog 提供的 driver,保留不变
        'driver' => 'daily',
        // channel 名称，要与数组键名保持一致
        'name'   => 'daily',
        // 日志存储路径，及日志文件命名
        'path'   => storage_path('logs/laralog-winapp-api.log'),
        // 指定使用的日志格式化组件类
        'tap'    => [Shallowman\Laralog\Formatter\LaralogFormatter::class],
        'level'  => 'info',
        // 日志文件保留天数
        'days'   => 7,
    ],
    ```
    
- 配置过滤敏感信息的键值，如需新增过滤的敏感信息，在 `config/laralog.php` 中 `except` 键对应的数组中，增加待过滤的请求参数键值

    ```php
    return [
        'except' => [
            'password',
            'password_information',
            'password_confirm',
            'something_to_except',
        ],
    ];
    ```

## `Laravel` 开发时如何记日志

- 日志记录保持不变，如下使用默认 `channel` 记录日志
```php
Log::info('log message', $context);
```

- 使用自定义 `channel` 写日志

```php
Log::channel('channel')->info('message', $context);
```