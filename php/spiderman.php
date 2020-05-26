<?php

use simple_html_dom;

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
    protected $result = '';

    public function __construct($url, $options = [])
    {
        $this->url = $this->cleanUpURL($url);
        $this->printAll();
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
    }

    public function setURL($url = '')
    {
        $this->url = $this->cleanUpURL($url);
    }

    public function setScheme($scheme = '')
    {
        $this->validate($scheme, 'scheme');
        $this->scheme = $scheme;
    }

    public function setHost($host = '')
    {
        $this->validate($host, 'host');
        $this->host = $host;
    }

    public function setEndpoint($endpoint = '')
    {
        $this->validate($endpoint, 'endpoint');
        $this->endpoint = $endpoint;
    }

    public function setQueryString($queries = '')
    {
        $this->validate($queries, 'queries');
        $this->queries = explode('&', $queries);
    }

    public function singleWebHit()
    {
        $this->setUpCURL();
        $response = curl_exec($this->curl);
        $this->closeCURL();
        $this->response = $response;
        return $response;
    }

    public function getElementById($id, $response = null)
    {
        if ($response === null) {
            $response = $this->response;
        }
        // s for case-insensitive
        $pattern = '/<(\w+)[^>]*id="' . $id . '"[^>]*>(.*?)<\/\1>/s';
        preg_match_all($pattern, $response, $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'tag' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function getElementsByTagName($tag, $response = null)
    {
        if ($response === null) {
            $response = $this->response;
        }
        // s for case-insensitive
        $pattern = '/<' . $tag . '(.*?)>(.*?)<\/' . $tag . '>/s';
        preg_match_all($pattern, $response, $this->result);
        $results = [
            'outerHTML' => $this->result[0],
            'attributes' => $this->result[1],
            'innerHTML' => $this->result[2]
        ];
        return $results;
    }

    public function setQueryArray($queries = [])
    {
        $this->validate($queries, 'queries');
        $this->queries = $queries;
    }

    public function validate($what = null, $which)
    {
        // check null
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
            default:
                $this->ThrowError();
                break;
        }

        $this->result = true;
    }

    protected function setUpCURL()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt_array($this->curl, $this->options);
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
            $this->endpoint = $parsed['path'] or $this->ThrowError('type');
            $this->queries = explode('&', $parsed['query']) or $this->ThrowError('type');
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
