<?php

class LogParser
{
    private const ACCESS_LOG_PATTERN = '/^\S+ \S+ \S+ \[.*?\] "\S+ (\S+).*?" (\d+) (\d+) ".*?" "(.*?)"/';

    private const CRAWLERS_BOT_NAMES = [
        'Google' => 'Googlebot',
        'Yandex' => 'YandexBot',
    ];

    /** @var string[] */
    private $_log;

    /** @var int */
    private $_viewsNumber = 0;
    /** @var string[] */
    private $_urls = [];
    /** @var int */
    private $_traffic = 0;
    /** @var int[] */
    private $_crawlers = [
        'Google' => 0,
        'Bing' => 0,
        'Baidu' => 0,
        'Yandex' => 0,
    ];
    /** @var int[] */
    private $_statusCodesCounts = [];

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->_log = file($path);
        if (!$this->_log) {
            throw (new Exception("Can not read file $path"));
        }
    }

    /**
     * @return string
     */
    public function parseFile(): string
    {
        while (count($this->_log) > 0) {
            $this->parseString(array_shift($this->_log));
        }

        $result = [
            'views' => $this->_viewsNumber,
            'urls' => count($this->_urls),
            'traffic' => $this->_traffic,
            'crawlers' => $this->_crawlers,
            'statusCodes' => $this->_statusCodesCounts,
        ];

        return json_encode($result);
    }

    /**
     * @param string $string
     */
    private function parseString(string $string): void
    {
        if (preg_match(self::ACCESS_LOG_PATTERN, $string, $logParts)) {
            $url = $logParts[1];
            $statusCode = $logParts[2];
            $traffic = $logParts[3];
            $client = $logParts[4];

            $this->_viewsNumber++;
            $this->addUrl($url);
            $this->_traffic += $traffic;
            $this->findCrawler($client);
            $this->increaseStatusCodeCount($statusCode);
        } else {
            throw (new Exception("Incorrect string in log: $string"));
        }
    }

    /**
     * @param string $url
     */
    private function addUrl(string $url): void
    {
        if (!in_array($url, $this->_urls)) {
            $this->_urls[] = $url;
        }
    }

    /**
     * @param string $client
     */
    private function findCrawler(string $client): void
    {
        foreach (self::CRAWLERS_BOT_NAMES as $crawler => $crawlerBotName) {
            if (strpos($client, $crawlerBotName) !== false) {
                $this->_crawlers[$crawler]++;
                break;
            }
        }
    }

    private function increaseStatusCodeCount($statusCode): void
    {
        if (!array_key_exists($statusCode, $this->_statusCodesCounts)) {
            $this->_statusCodesCounts[$statusCode] = 0;
        }

        $this->_statusCodesCounts[$statusCode]++;
    }
}

/** @var string[] $argv */

$parser = new LogParser($argv[1]);
echo $parser->parseFile();
