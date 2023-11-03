<?php
declare (strict_types=1);

namespace app\libs;

use DateTime;
use DateTimeZone;
use Exception;
use think\App;
use think\contract\LogHandlerInterface;
use think\facade\Request;

/**
 * 本地化调试输出到文件
 */
class MyselfLog implements LogHandlerInterface
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'time_format' => 'c',
        'single' => false,
        'file_size' => 2097152,
        'path' => '',
        'apart_level' => [],
        'max_files' => 0,
        'json' => false,
        'json_options' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        'format' => '[%s][%s] %s',
    ];

    protected $app;

    // 实例化并传入参数
    public function __construct(App $app, $config = [])
    {
        $this->app = $app;

        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        if (empty($this->config['format'])) {
            $this->config['format'] = '[%s][%s] %s';
        }

        if (empty($this->config['path'])) {
            $this->config['path'] = $app->getRuntimePath() . 'log';
        }

        if (substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log): bool
    {
        $destination = $this->getMasterLogFile();


        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        $info = [];

        // 日志信息封装
        $time = DateTime::createFromFormat('0.u00 U', microtime())->setTimezone(new DateTimeZone(date_default_timezone_get()))->format($this->config['time_format']);

        $request = Request::instance();
        //新增
        $requestInfo = [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'host' => $request->host(),
            'uri' => $request->url()
        ];

        if (isset($log['sql'][0]) && strpos('CONNECT', $log['sql'][0])) {

        }

        if (!$this->config['json']) {

            $debugInfo = [
                'param' => '[ PARAM ] ' . var_export($request->param(), true),
                'header' => '[ HEADER ] ' . var_export($request->header(), true)
            ];
            foreach ($debugInfo as $row) {
                array_unshift($info, $row);
            }

            // 增加额外的调试信息
            $runtime = round(microtime(true) - $this->app->getBeginTime(), 10);
            $reqs = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
            $memory_use = number_format((memory_get_usage() - $this->app->getBeginMem()) / 1024, 2);
            $time_str = '[运行时间：' . number_format($runtime, 6) . 's] [吞吐率：' . $reqs . 'req/s]';
            $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
            $file_load = ' [文件加载：' . count(get_included_files()) . ']';
            array_unshift($info, $time_str . $memory_str . $file_load);


            array_unshift($info, "---------------------------------------------------------------\r\n[{$time}] {$requestInfo['ip']} {$requestInfo['method']} {$requestInfo['host']}{$requestInfo['uri']}");
        }

        foreach ($log as $type => $val) {
            $message = [];
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }

                $message[] = $this->config['json'] ?
                    json_encode(['time' => $time, 'type' => $type, 'msg' => $msg], $this->config['json_options']) :
                    sprintf($this->config['format'], $time, $type, $msg);
            }

            if (true === $this->config['apart_level'] || in_array($type, $this->config['apart_level'])) {
                //这一句很关键，可以给mysql或者其他独立的日志，也加上请求和时间等信息
                array_unshift($message, "---------------------------------------------------------------\r\n[{$time}] {$requestInfo['ip']} {$requestInfo['method']} {$requestInfo['host']}{$requestInfo['uri']}");
                // 独立记录的日志级别
                $filename = $this->getApartLevelFile($path, $type);
                $this->write($message, $filename);
                continue;
            }

            $info[$type] = $message;
        }

        if ($info) {
            return $this->write($info, $destination);
        }

        return true;
    }

    /**
     * 获取主日志文件名
     * @access public
     * @return string
     */
    protected function getMasterLogFile(): string
    {

        if ($this->config['max_files']) {
            $files = glob($this->config['path'] . '*.log');

            try {
                if (count($files) > $this->config['max_files']) {
                    unlink($files[0]);
                }
            } catch (Exception $e) {
                //
            }
        }

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $destination = $this->config['path'] . $name . '.log';
        } else {

            if ($this->config['max_files']) {
                $filename = date('Ymd') . '.log';
            } else {
                $filename = date('Ym') . DIRECTORY_SEPARATOR . date('d') . '.log';
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    /**
     * 获取独立日志文件名
     * @access public
     * @param string $path 日志目录
     * @param string $type 日志类型
     * @return string
     */
    protected function getApartLevelFile(string $path, string $type): string
    {

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';

            $name .= '_' . $type;
        } elseif ($this->config['max_files']) {
            $name = date('Ymd') . '_' . $type;
        } else {
            $name = date('d') . '_' . $type;
        }

        return $path . DIRECTORY_SEPARATOR . $name . '.log';
    }

    /**
     * 日志写入
     * @access protected
     * @param array $message 日志信息
     * @param string $destination 日志文件
     * @return bool
     */
    protected function write(array $message, string $destination): bool
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        $this->checkLogSize($destination);

        $info = [];

        foreach ($message as $type => $msg) {
            $info[$type] = is_array($msg) ? implode(PHP_EOL, $msg) : $msg;
        }


        $message = implode(PHP_EOL, $info) . PHP_EOL;

        fwrite(STDOUT, $message);
        return error_log($message, 3, $destination);
    }

    /**
     * 检查日志文件大小并自动生成备份文件
     * @access protected
     * @param string $destination 日志文件
     * @return void
     */
    protected function checkLogSize(string $destination): void
    {
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            try {
                rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
            } catch (Exception $e) {
                //
            }
        }
    }
}
