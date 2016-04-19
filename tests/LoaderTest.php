<?php
/**
 * Created by PhpStorm.
 * User: zhaoqing
 * Date: 16/4/19
 * Time: 下午11:28
 */

namespace Comos\Config;


class LoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $unreadableJsonFile;
    protected function setUp()
    {
        parent::setUp();
        $this->unreadableJsonFile = __DIR__.'/conf-unreadable.json';
        touch($this->unreadableJsonFile);
        chmod($this->unreadableJsonFile, 0077);
    }

    protected function tearDown()
    {
        unlink($this->unreadableJsonFile);
        parent::tearDown();
    }

    public function testFromJsonFile()
    {
        $jsonFile = __DIR__.'/conf.json';
        $conf = Loader::fromJsonFile($jsonFile);
        $this->assertInstanceOf(Config::class, $conf);
        $this->assertEquals('1', $conf->rstr('b'));
    }

    /**
     * @expectedException \Comos\Config\Exception
     * @expectedExceptionMessage cannot find the conf file
     */
    public function testFromJsonFile_CannotFindFile()
    {
        $jsonFile = __DIR__.'/conf-x.json';
        Loader::fromJsonFile($jsonFile);
    }
    /**
     * @expectedException \Comos\Config\Exception
     * @expectedExceptionMessage cannot read conf file
     */
    public function testFromJsonFile_FailToReadFile()
    {
        Loader::fromJsonFile($this->unreadableJsonFile);
    }
    /**
     * @expectedException \Comos\Config\Exception
     * @expectedExceptionMessage bad format
     */
    public function testFromJsonFile_BadFormat()
    {
        Loader::fromJsonFile(__DIR__.'/bad.json');
    }
}
 