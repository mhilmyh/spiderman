<?php

class Spiderman
{
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

    public function __construct($url, $options = [])
    {
        $this->url = $this->cleanUpURL($url);
        $this->options = $options;
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function clear()
    {
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
        return $this->visited;
    }

    public function resetVisited()
    {
        $this->visited = [];
    }

    public function singleWebHit($url = '')
    {
        $this->setUpCURL($url);
        $response = curl_exec($this->curl);
        $this->setInfoCURL();
        $this->closeCURL();
        $this->response = $response;
        return $response;
    }

    public function crawlingPageLinks($url = '', $maxDepth = 5, $depth = 0)
    {
        if ($this->visited[$url] || $maxDepth === $depth) {
            return;
        }
        $url = $url or $this->url;
        $this->setUpCURL($url);
        $response = curl_exec($this->curl);

        $visited[$url] = true;

        $this->setInfoCURL();
        $this->closeCURL();
        $this->response = $response;
        return $response;
    }


    public function getAttribute($attr = 'href', $response = null)
    {
        if ($response === null) {
            $response = $this->response;
        }
        $pattern = '/<(\w+)[^>]*' . $attr . '="([^">]*)"[^>]*>/s';
        preg_match_all($pattern, $response, $this->result);
        return $this->result;
    }

    public function getElementById($id = '', $response = null)
    {
        if ($response === null) {
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
        if ($response === null) {
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
        if ($response === null) {
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
        if ($response === null) {
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
        curl_setopt($this->curl, CURLOPT_URL, $url or $this->url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        if ($this->options != null) curl_setopt_array($this->curl, $this->options);
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

    protected function cleanUpURL($url)
    {
        try {
            $parsed = parse_url($url);
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

    private function printAll($success = true)
    {
        $color = ["\033[31m", "\033[32m"];
        $closing = "\033[37m\r";
        echo $color[$success] . $this->url . $closing . PHP_EOL;
        echo $color[$success] . $this->scheme . $closing . PHP_EOL;
        echo $color[$success] . $this->host . $closing . PHP_EOL;
        echo $color[$success] . $this->endpoint . $closing . PHP_EOL;
        echo $color[$success] . implode("&", $this->queries) . $closing . PHP_EOL;
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
}
