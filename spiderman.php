<?php

class Spiderman
{
    protected $db = null;
    protected $curl = null;
    protected $url = '';
    protected $scheme = '';
    protected $validScheme = ['http', 'https'];
    protected $host = '';
    protected $endpoint = '';
    protected $queries = [];
    protected $options = [];
    protected $response = '';
    protected $info = [];
    protected $result = '';
    protected $visited = [];

    public function __construct($url = null, $db, $options = [])
    {
        $this->url = $url === null ? '' : $this->cleanUpURL($url);
        $this->options = is_array($options) ? $options : explode(',', $options);
        $this->db = is_array($db) ? $db : explode(',', $db);
        $this->connection();
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function clear()
    {
        $this->db = [];
        $this->curl = null;
        $this->url = '';
        $this->scheme = '';
        $this->host = '';
        $this->endpoint = '';
        $this->queries = [];
        $this->options = [];
        $this->response = '';
        $this->info = [];
        $this->result = '';
        $this->visited = [];
    }

    private function connection()
    {
        $db = null;
        try {
            $db = new mysqli(
                $this->db["DB_HOST"],
                $this->db["DB_USERNAME"],
                $this->db["DB_PASSWORD"],
                $this->db["DB_DATABASE"]
            );
            if (!$db) $this->printAll('Connect to database fail', false);
            return $db;
        } catch (Error $error) {
            $this->printAll('Connect to database fail' . $error, false);
            return null;
        }
    }

    public function setURL($url = '')
    {
        $this->url = $this->cleanUpURL($url);
    }

    public function getURL()
    {
        return $this->url;
    }

    public function setScheme($scheme = '')
    {
        $this->validate($scheme, 'scheme');
        $this->scheme = $scheme;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setHost($host = '')
    {
        $this->validate($host, 'host');
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setEndpoint($endpoint = '')
    {
        $this->validate($endpoint, 'endpoint');
        $this->endpoint = $endpoint;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setQueryString($queries = '')
    {
        $this->validate($queries, 'queries');
        $this->queries = explode('&', $queries);
    }

    public function getQueryString()
    {
        return implode('&', $this->queries);
    }

    public function setQueryArray($queries = [])
    {
        $this->validate($queries, 'queries');
        $this->queries = $queries;
    }

    public function getQueryArray()
    {
        return $this->queries;
    }

    public function setOptions($options = [])
    {
        $this->validate($options, 'options');
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getVisited()
    {
        return array_keys($this->visited);
    }

    public function resetVisited()
    {
        $this->visited = [];
    }

    public function singleWebHit($url = '')
    {
        $this->setUpCURL($url);
        $response = curl_exec($this->curl);
        $this->closeCURL();
        $this->response = $response;
        return $response;
    }

    public function crawlingPageLinks($url = null, $maxDepth = 1, $saveToDB = false, $saveToFile = false, $depth = 0)
    {
        if ($maxDepth + 1 === $depth) {
            return;
        } else if (isset($this->visited[$url]) && $this->visited[$url] === true) {
            return;
        }
        $url = $url === null ? strtolower($this->url) : strtolower($url);
        $this->setUpCURL($url);
        $response = curl_exec($this->curl);
        $this->closeCURL();
        $this->visited[$url] = true;
        echo $url . PHP_EOL;
        $parsed = parse_url($url);
        $pathfile = '';
        if ($saveToFile) {
            $pathfile = $this->storeResponse($response, $url);
        }
        if ($saveToDB) {
            $data = [
                'scheme' => $parsed['scheme'],
                'host' => $parsed['host'],
                'endpoint' => $parsed['path'],
                'storage' => $pathfile
            ];
            $sql = $this->constructInsertQuery($data);
            $sql = preg_replace('/\s+/', ' ', $sql);
            $db = $this->connection();
            $db->query($sql);
            $db->close();
        }
        $queue = $this->getAttributeValues('href', $response)['value'];
        $size = count($queue);
        for ($i = 0; $i < $size; $i++) {
            if (strlen($queue[$i]) >= 1 && $queue[$i][0] === '#') {
                $queue[$i] = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] . $queue[$i];
            } else if (strlen($queue[$i]) >= 2 && $queue[$i][0] === '/' && $queue[$i][1] === '/') {
                $queue[$i] = $parsed['scheme'] . ':' . $queue[$i];
            } else if (strlen($queue[$i]) >= 1 && $queue[$i][0] === '/') {
                $queue[$i] = $parsed['scheme'] . '://' . $parsed['host'] . $queue[$i];
            } else if (is_int(strpos($queue[$i], 'javascript'))) {
                unset($queue[$i]);
            }
        }
        while (!empty($queue)) {
            $url = array_shift($queue);
            $this->crawlingPageLinks($url, $maxDepth, $saveToDB, $saveToFile, $depth + 1);
        }
    }


    public function getAttributeValues($attr = '', $response = null, $which = '')
    {
        if ($response == null) {
            $response = $this->response;
        }
        if ($which == null) {
            $which = '([^">]*)';
        }

        $pattern = '/<(\w+)[^>]*' . $attr . '="' . $which . '"[^>]*>/s';
        preg_match_all($pattern, htmlspecialchars_decode($response), $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'tag' => $this->result[1],
            'value' => $this->result[2]
        ];
        return $results;
    }

    public function getElementById($id = '', $response = null)
    {
        if ($response == null) {
            $response = $this->response;
        }
        $pattern = '/<(\w+)[^>]*id="' . $id . '"[^>]*>(.*?)<\/\1>/s';
        preg_match($pattern, htmlspecialchars_decode($response), $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'tag' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function getElementsByName($name = '', $response = null)
    {
        if ($response == null) {
            $response = $this->response;
        }
        $pattern = '/<(\w+)[^>]*name="' . $name . '"[^>]*>(.*?)<\/\1>/s';
        preg_match_all($pattern, htmlspecialchars_decode($response), $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'tags' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function getElementsByClassName($class = '', $response = null)
    {
        if ($response == null) {
            $response = $this->response;
        }
        $pattern = '/<(\w+)[^>]*class="[^">]*' . $class . '[^">]*"[^>]*>(.*?)<\/\1>/s';
        preg_match_all($pattern, htmlspecialchars_decode($response), $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'tags' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function getElementsByTagName($tag = '', $response = null)
    {
        if ($response == null) {
            $response = $this->response;
        }
        $pattern = '/<' . $tag . '(.*?)>(.*?)<\/' . $tag . '>/s';
        preg_match_all($pattern, htmlspecialchars_decode($response), $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'attributes' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function validate($what = null, $which)
    {
        $what or $this->ThrowError('argument');
        switch ($which) {
            case 'scheme':
                in_array($what, $this->validScheme) or $this->ThrowError('assertion');
                break;
            case 'host':
                // Not implemented yet
                break;
            case 'endpoint':
                // Not implemented yet
                break;
            case 'queries':
                // Not implemented yet
                break;
            case 'options':
                // Not implemented yet
                break;
            default:
                $this->ThrowError();
                break;
        }

        $this->result = true;
    }

    protected function setUpCURL($url = '')
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $url == null ? $this->url : $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'spiderman');
        if ($this->options != null) {
            curl_setopt_array($this->curl, $this->options);
            $this->printAll('curl_setopt_array is executed');
        }
    }

    protected function setInfoCURL()
    {
        $this->info['status'] = curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE);
        $this->info['cookies'] = curl_getinfo($this->curl, CURLINFO_COOKIELIST);
        $this->info['content-type'] = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);
        $this->info['size'] = curl_getinfo($this->curl, CURLINFO_REQUEST_SIZE);
        $this->info['ip'] = curl_getinfo($this->curl, CURLINFO_LOCAL_IP);
        $this->info['cert'] = curl_getinfo($this->curl, CURLINFO_CERTINFO);
    }

    protected function closeCURL()
    {
        curl_close($this->curl);
    }

    protected function cleanUpURL($url = '')
    {
        try {
            $parsed = parse_url(strtolower($url));
            $this->scheme = $parsed['scheme'] or $this->ThrowError('type');
            $this->host = $parsed['host'] or $this->ThrowError('type');
            $this->endpoint = isset($parsed['path']) ? $parsed['path'] : '';
            $this->queries = isset($parsed['query']) ? explode('&', $parsed['query']) : [];
            $this->result = $parsed;
            return trim($url);
        } catch (TypeError $error) {
            echo "\033[31m" . "URL format is wrong : " . $url . "\033[37m\r" . PHP_EOL;
            echo $error;
            return null;
        }
    }

    private function printAll($messages = null, $success = true)
    {
        $color = ["\033[31m", "\033[32m"];
        $closing = "\033[37m\r";
        if (is_array($messages)) {
            foreach ($messages as $msg) {
                echo $color[$success] . $msg . $closing . PHP_EOL;
            }
        } else if (is_string($messages)) {
            echo $color[$success] . $messages . $closing . PHP_EOL;
        }
    }

    private function ThrowError($type = null)
    {
        switch ($type) {
            case 'type':
                throw new TypeError();
                break;
            case 'assertion':
                throw new AssertionError();
                break;
            case 'parse':
                throw new ParseError();
                break;
            case 'argument':
                throw new ArgumentCountError();
                break;
            default:
                throw new ErrorException();
                break;
        }
    }

    public function constructInsertQuery($arrays = [])
    {
        $backticks = array_map(function ($element) {
            return '`' . $element . '`';
        }, array_keys($arrays));
        $columns = implode(', ', $backticks);
        $quoted = array_map(function ($element) {
            return '"' . $element . '"';
        }, array_values($arrays));
        $values = implode(', ', $quoted);
        $string = 'INSERT INTO website (' . $columns . ') VALUES (' . $values . ')';
        return $string;
    }

    public function storeResponse($file, $url = '')
    {
        if ($url === '') {
            $url = $this->url;
        }
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $this->generateFilename($url);
        file_put_contents($filename, $file);
        return $filename;
    }

    public function generateFilename($url = '')
    {
        if ($url === '') {
            $url = $this->url;
        }
        $parsed = parse_url($url);
        $path = [];
        if (isset($parsed['path'])) {
            $path = explode('.', $parsed['path']);
        }
        $format = count($path) === 2 ? $path[1] : 'html';
        $hashed = hash("md5", $url);
        $filename = date("Y-m-d") . '-' . $hashed . '.' . $format;
        $filename = rtrim($filename, ' \t\n\r\0\x0B\x2F');
        return $filename;
    }
}
