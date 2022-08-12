<?php

namespace Test;

use App\LogParser;
use PHPUnit\Framework\TestCase;

class LogParserTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCorrectFile(): void
    {
        $object = new LogParser('storage/access_standard.log');

        $result = $object->parseFile();
        $this->assertEquals('{"views":16,"urls":5,"traffic":212816,"crawlers":{"Google":2,"Bing":0,"Baidu":0,"Yandex":0},"statusCodes":{"200":14,"301":2}}', $result);
    }

    /**
     * @throws \Exception
     */
    public function testIncorrectFile(): void
    {
        $object = new LogParser('storage/access_incorrect.log');

        $this->expectException(\Exception::class);
        $object->parseFile();
    }
}