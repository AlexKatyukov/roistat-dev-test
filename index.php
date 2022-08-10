<?php

class LogParser
{
    private const CRAWLERS = [
        'Google' => 'Googlebot',
        'Yandex' => 'YandexBot',
    ];

    /** @var string[] */
    private $log;

    /** @var int */
    private $viewsNumber = 0;
    /** @var string[] */
    private $urls = [];
    /** @var int */
    private $traffic = 0;
    /** @var int[] */
    private $crawlers = [
        'Google' => 0,
        'Bing' => 0,
        'Baidu' => 0,
        'Yandex' => 0,
    ];
    /** @var int[] */
    private $statusCodes = [];

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->log = file($path);
    }

    /**
     * @return string
     */
    public function parse(): string
    {
        while (count($this->log) > 0) {
            $this->parseString(array_shift($this->log));
        }

        $result = [
            'views' => $this->viewsNumber,
            'urls' => count($this->urls),
            'traffic' => $this->traffic,
            'crawlers' => $this->crawlers,
            'statusCodes' => $this->statusCodes,
        ];

        return json_encode($result);
    }

    /**
     * @param string $string
     */
    private function parseString(string $string): void
    {
        if (preg_match('/^\S+ \S+ \S+ \[.*?\] "\S+ (\S+).*?" (\d+) (\d+) ".*?" "(.*?)"/', $string, $m)) {
            $url = $m[1];
            $statusCode = $m[2];
            $traffic = $m[3];
            $client = $m[4];
        }

        $this->viewsNumber++;
        $this->addUrl($url);
        $this->traffic += $traffic;
        $this->findCrawler($client);
        $this->statusCodes[$statusCode]++;
    }

    /**
     * @param string $url
     */
    private function addUrl(string $url): void
    {
        if (!in_array($url, $this->urls)) {
            $this->urls[] = $url;
        }
    }

    /**
     * @param string $client
     */
    private function findCrawler(string $client): void
    {
        foreach (self::CRAWLERS as $crawler => $crawlerBotName) {
            if (strpos($client, $crawlerBotName) !== false) {
                $this->crawlers[$crawler]++;
                break;
            }
        }
    }
}

/** @var string[] $argv */

$parser = new LogParser($argv[1]);
echo $parser->parse();
