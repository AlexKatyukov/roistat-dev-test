<?php

namespace App;

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
    private $_filePosition;

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
     * @throws \Exception
     */
    public function __construct(string $path)
    {
        $this->_log = fopen($path, 'r');

        if (!$this->_log) {
            throw (new \Exception("Can not read file $path"));
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function parseFile(): string
    {
        while ($this->_filePosition !== ftell($this->_log)) {
            $string = $this->getString();
            $this->parseString($string);
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
     * @return string
     */
    private function getString(): string
    {
        $this->_filePosition = ftell($this->_log);
        return fgets($this->_log);
    }

    /**
     * @param string $string
     * @throws \Exception
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
        } elseif ($string !== '') {
            throw (new \Exception("Incorrect string in log: $string"));
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

    /**
     * @param int $statusCode
     */
    private function increaseStatusCodeCount(int $statusCode): void
    {
        if (!array_key_exists($statusCode, $this->_statusCodesCounts)) {
            $this->_statusCodesCounts[$statusCode] = 0;
        }

        $this->_statusCodesCounts[$statusCode]++;
    }
}