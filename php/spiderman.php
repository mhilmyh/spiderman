<?php

use simplehtmldom_1_5\simple_html_dom;

class Spiderman extends simple_html_dom
{
    protected $curl = null;
    protected $url = '';
    protected $scheme = '';
    protected $validScheme = ['http', 'https'];
    protected $host = '';
    protected $endpoint = '';
    protected $queries = [];
    protected $options = [];

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
        return $response;
    }

    public function setUpCURL()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt_array($this->curl, $this->options);
    }

    public function closeCURL()
    {
        curl_close($this->curl);
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
    }

    protected function cleanUpURL($url)
    {
        try {
            $parsed = parse_url($url);
            $this->scheme = $parsed['scheme'] or $this->ThrowError('type');
            $this->host = $parsed['host'] or $this->ThrowError('type');
            $this->endpoint = $parsed['path'] or $this->ThrowError('type');
            $this->queries = explode('&', $parsed['query']) or $this->ThrowError('type');
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

    // helper functions
    // -----------------------------------------------------------------------------
    // get html dom from file
    // $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
    public function file_get_html($url, $use_include_path = false, $context = null, $offset = 0, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    {
        // We DO force the tags to be terminated.
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        $contents = file_get_contents($url, $use_include_path, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        //$contents = retrieve_url_contents($url);
        if (empty($contents) || strlen($contents) > MAX_FILE_SIZE) {
            return false;
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);
        return $dom;
    }

    // get html dom from string
    public function str_get_html($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    {
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }

    // dump html dom tree
    public function dump_html_tree($node, $show_attr = true, $deep = 0)
    {
        $node->dump($node);
    }
}
