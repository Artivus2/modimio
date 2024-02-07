<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Logs;



class NginxParse
{
    protected static $defaultFormat = '%h %l %u %t "%r" %>s %b';
    protected $pcreFormat;
    protected $handler = null;
    protected $fbuffer = [];
    protected $offsetLines = 0;
    protected $limitLines = 0;
    protected $patterns = [
        '%%' => '(?P<percent>\%)',
        '%a' => '(?P<remoteIp>)',
        '%A' => '(?P<localIp>)',
        '%h' => '(?P<host>[a-zA-Z0-9\-\._:]+)',
        '%l' => '(?P<logname>(?:-|[\w-]+))',
        '%m' => '(?P<requestMethod>OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT|PATCH|PROPFIND)',
        '%p' => '(?P<port>\d+)',
        '%r' => '(?P<request>(?:(?:[A-Z]+) .+? HTTP/[1-3].(?:0|1))|-|)',
        '%t' => '\[(?P<time>\d{2}/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/\d{4}:\d{2}:\d{2}:\d{2} (?:-|\+)\d{4})\]',
        '%u' => '(?P<user>(?:-|[\w-]+))',
        '%U' => '(?P<URL>.+?)',
        '%v' => '(?P<serverName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%V' => '(?P<canonicalServerName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%>s' => '(?P<status>\d{3}|-)',
        '%b' => '(?P<responseBytes>(\d+|-))',
        '%T' => '(?P<requestTime>(\d+\.?\d*))',
        '%O' => '(?P<sentBytes>[0-9]+)',
        '%I' => '(?P<receivedBytes>[0-9]+)',
        '\%\{(?P<name>[a-zA-Z]+)(?P<name2>[-]?)(?P<name3>[a-zA-Z]+)\}i' => '(?P<Header\\1\\3>.*?)',
        '%D' => '(?P<timeServeRequest>[0-9]+)',
    ];

    public static function getDefaultFormat()
    {
        return self::$defaultFormat;
    }

    public function __construct($fileName = null, $format = null, $offset = 0, $limit = 10)
    {

        $this->offsetLines = $offset;
        $this->limitLines = $limit;

        if(!($this->handler = fopen($fileName, "rb"))) {
            throw new \Exception("Cannot open the file");
        }

        // Set IPv4 & IPv6 recognition patterns
        $ipPatterns = implode('|', array(
            'ipv4' => '(((25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]))',
            'ipv6full' => '([0-9A-Fa-f]{1,4}(:[0-9A-Fa-f]{1,4}){7})', // 1:1:1:1:1:1:1:1
            'ipv6null' => '(::)',
            'ipv6leading' => '(:(:[0-9A-Fa-f]{1,4}){1,7})', // ::1:1:1:1:1:1:1
            'ipv6mid' => '(([0-9A-Fa-f]{1,4}:){1,6}(:[0-9A-Fa-f]{1,4}){1,6})', // 1:1:1::1:1:1
            'ipv6trailing' => '(([0-9A-Fa-f]{1,4}:){1,7}:)', // 1:1:1:1:1:1:1::
        ));
        $this->patterns['%a'] = '(?P<remoteIp>'.$ipPatterns.')';
        $this->patterns['%A'] = '(?P<localIp>'.$ipPatterns.')';
        $this->setFormat($format ?: self::getDefaultFormat());
    }

    public function readLog($countLine = 10)
    {
        if(!$this->handler) {
            throw new Exception("Invalid file pointer");
        }

        while(!feof($this->handler)) {
            $this->fbuffer[] = fgets($this->handler);
            $countLine--;
            if($countLine == 0) break;
        }

        return $this->fbuffer;
    }

    public function setOffset($line = 0)
    {
        if(!$this->handler) {
            throw new \Exception("Invalid file pointer");
        }

        while(!feof($this->handler) && $line--) {
            fgets($this->handler);
        }
    }

    public function addPattern($placeholder, $pattern)
    {
        $this->patterns[$placeholder] = $pattern;
    }

    public function setFormat($format)
    {
        // strtr won't work for "complex" header patterns
        // $this->pcreFormat = strtr("#^{$format}$#", $this->patterns);
        $expr = "#^{$format}$#";
        foreach ($this->patterns as $pattern => $replace) {
            $expr = preg_replace("/{$pattern}/", $replace, $expr);
        }
        $this->pcreFormat = $expr;
    }

    public function parse($line)
    {
        if (!preg_match($this->pcreFormat, $line, $matches)) {
            throw new \Exception("Error parsing line, check offset and limits");
        }
        $entry = new \stdClass();
        foreach (array_filter(array_keys($matches), 'is_string') as $key) {
            if ('time' === $key && true !== $stamp = strtotime($matches[$key])) {
                $entry->stamp = $stamp;
            }
            $entry->{$key} = $matches[$key];
        }
        return $entry;
    }

    public function getPCRE()
    {
        return (string) $this->pcreFormat;
    }

    public function getLines()
    {
        if(!$this->handler) {
            throw new \Exception("Invalid file pointer");
        }
        $lines = 0;
        while(!feof($this->handler)) {
            $lines += substr_count(fread($this->handler, 8192), "\n");
        }
        fclose($this->handler);
        return $lines;
    }

    public function worker()
    {
        $this->setOffset($this->offsetLines);
        $result = $this->readLog($this->limitLines);
        $entry = [];
        foreach ($result as $line) {
            $entry[] = $this->parse($line);
            // [host] => 217.118.64.117
            //         [logname] => -
            //         [user] => -
            //         [stamp] => 1553138939
            //         [time] => 21/Mar/2019:06:28:59 +0300
            //         [request] => GET /assets/e7d282d/img/icon-basket.svg HTTP/1.1
            //         [status] => 200
            //         [sentBytes] => 1976
            //         [HeaderReferer] => https://tanki.modimio.ru/
            //         [HeaderUserAgent]
            

        }
        return ['data' => $entry ?? null, 'totalLines' => $this->getLines() ?? null];
    }
}

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LoadlogsController extends Controller
{
    public function actionIndex(){
        
        
        $result = $this->getzip();
        $array = $this->parselog($result);
        foreach ($array as $entry) {
            $logs = new Logs;
            $logs->ip = $entry['host'];
            $logs->date = $entry['stamp'];
            $logs->url = $entry['HeaderReferer'];
            $logs->useragent = $entry['HeaderUserAgent'];
            $logs->save;
        }

    }

    
    protected function getzip() {
        //находим zip файл
        //распаковываем его
        //переименовываем zip в checked
        //отдаем список файлов/файл для обработки в функцию parselog
        $result = array();
        $dir = "uploads";
        $file_lists = scandir($dir , SCANDIR_SORT_DESCENDING);
        $extzip = "zip";
        $extnew = "checked";
        
        //print_r($file_lists);
        foreach ($file_lists as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            print_r("\nINFO: " . $ext);
            if ($ext == $extzip) {
                print_r("\nINFO: найден файл: " . $dir . $file);
                $zip = new \ZipArchive();
                if ($zip->open($dir .DIRECTORY_SEPARATOR. $file) === true) {
                    $zip->extractTo($dir);
                    $zip->close();
                    rename($dir .DIRECTORY_SEPARATOR.$file, $dir . "/".$file . "." . $extnew);
                    print_r("\nINFO: распакован zip:");
                    } else {
                        print_r("\nINFO: ошибка");
                    }
            
            }
        }
        $file_lists = scandir($dir , SCANDIR_SORT_DESCENDING);
        foreach ($file_lists as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if($file === '.' || $file === '..') {continue;}
            print_r("\nINFO: " . $ext);
            if ($ext === $extzip || $ext === $extnew) {
                print_r("\nINFO: " . $file);
                //$result[] = $dir .DIRECTORY_SEPARATOR. $file;
            } else {
                print_r("\nINFO: " . $file ."\n");
                $result[] = $dir .DIRECTORY_SEPARATOR. $file;
            }



        }
        

        return $result;
    }

    protected function parselog($result = null){
        //print_r($result);
        $data = array();
        foreach ($result as $file) {
            echo 'INFO: Парсинг файла: '.$file.", размер: ".filesize($file)." байт.... \n";
            //$this->stdout('INFO: Парсинг файла: '.$file.", размер: ".filesize($file)." байт.... \n", Console::BOLD);
            $fd = fopen($file, 'r') or die("не удалось открыть файл");
            $handle = fopen($file, "r");
            $ic = 0;
            $ic_max = 10;  // stops after this number of rows
            $ip_re = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/';
            $buffer = new NginxParse($file, '%h %l %u %t "%r" %>s %O "%{Referer}i" \"%{User-Agent}i"', 0, 10);

            // Get array of data & data count
            $array = $buffer->worker();

            // Total lines in log file
            echo '<h1>'.$array['totalLines'].'</h1>';

            // Dump data array
            echo '<pre>';
            print_r($array['data']);
            echo '</pre>';
        }
            print_r($array);
        
        return $array;
        

        
    }
}
