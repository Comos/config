<?php
/**
 * Created by PhpStorm.
 * User: zhaoqing2
 * Date: 15/8/6
 * Time: 下午4:36
 */

namespace Comos\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testFromArray()
    {
        $this->assertTrue(Config::fromArray(array('a' => 1)) instanceof Config);
        $this->assertTrue(Config::fromArray(new \ArrayObject(array('a' => 1))) instanceof Config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromArray_InvalidArgument()
    {
        Config::fromArray('x');
    }

    /**
     * @dataProvider getParamMethodsProvider
     */
    public function testGetParamMethods($method, $data, $key, $default, $expectedValue)
    {
        $result = Config::fromArray($data)->$method($key, $default);
        $this->assertTrue($expectedValue === $result);
    }

    public function getParamMethodsProvider()
    {
        return array(
            //$method, $data, $key, $default, $expectedValue
            array('str', array('a' => '1', 'b' => 2), 'a', null, '1'),
            array('str', array('a' => '1', 'b' => 2), 'b', null, '2'),
            array('str', array('a' => '1', 'b' => 2), 'c', null, null),
            array('str', array('a' => '1', 'b' => 2), 'c', 'x', 'x'),
            array('str', array('a', 'b', 'c'), 2, null, 'c'),
            array('str', array('a', 'b', 'c'), '2', null, 'c'),
            array('int', array('a' => 1), 'a', null, 1),
            array('int', array('a' => 1.0), 'a', null, 1),
            array('int', array('a' => "1.0"), 'a', null, 1),
            array('int', array('a' => "1.0"), 'b', null, null),
            array('float', array('a' => "1.0"), 'a', null, 1.0),
            array('float', array('a' => "1.0"), 'b', 1.1, 1.1),
            array('float', array('c' => 2), 'c', 1.1, 2.0),
            array('float', array('c' => 2), 'c', 1.1, 2.0),
            array('float', array('c' => 0), 'c', 1.1, 0.0),
            array('bool', array('c' => 0), 'c', null, false),
            array('bool', array('c' => 'true'), 'c', null, true),
            array('bool', array('c' => 'False'), 'c', null, false),
        );
    }

    /**
     * @dataProvider dataProviderForTestGetParamMethods_TypeError
     * @expectedException Exception
     * @expectedExceptionMessage type error
     */
    public function testGetParamMethods_TypeError($method, $data, $key)
    {
        Config::fromArray($data)->$method($key);
    }

    public function dataProviderForTestGetParamMethods_TypeError()
    {
        return array(
            array('rint', array('a' => 'x'), 'a'),
            array('rint', array('a' => array()), 'a'),
            array('int', array('a' => false), 'a'),
            array('int', array('a' => 'x'), 'a'),
            array('float', array('a' => array()), 'a'),
            array('float', array('a' => new \stdClass()), 'a'),
            array('float', array('a' => true), 'a'),
            array('bool', array('a' => array()), 'a'),
            array('rbool', array('a' => array()), 'a'),
            array('bool', array('a' => new \stdClass()), 'a'),
        );
    }

    /**
     * @dataProvider getParamMethodsProvider_RestrictMode_MissRequiredField_DataProvider
     * @expectedException Exception
     */
    public function testGetParamMethods_RestrictMode_MissRequiredField($method, $data, $key)
    {
        Config::fromArray($data)->$method($key);
    }

    public function getParamMethodsProvider_RestrictMode_MissRequiredField_DataProvider()
    {
        return array(
            //$method, $data, $key
            array('rstr', array('a' => '1', 'b' => 2), 'c'),
            array('rstr', array('a' => '1', 'b' => 2), 'x'),
            array('rstr', array('a', 'b', 'c'), 3),
            array('rstr', array('a', 'b', null), 2),
            array('rint', array('a' => 1), 'c'),
            array('rint', array('a', 'x'), 3),
            array('rfloat', array('a', 'x'), 3),
            array('rbool', array('a', 'x'), 3),
        );
    }

    /**
     * @param string $method
     * @param array $data
     * @param string $key
     * @param string $expectedValue
     * @dataProvider dataProviderForGetParamMethods_RestrictMode
     */
    public function testGetParamMethods_RestrictMode($method, $data, $key, $expectedValue)
    {
        $result = Config::fromArray($data)->$method($key);
        $this->assertTrue($expectedValue === $result);
    }

    public function dataProviderForGetParamMethods_RestrictMode()
    {
        return array(
            //$method, $data, $key, $expectedValue
            array('rstr', array('a' => '1', 'b' => 2), 'b', '2'),
            array('rstr', array('a' => '1', 'b' => 2), 'a', '1'),
            array('rstr', array('a', 'b', 'c'), 2, 'c'),
            array('rint', array('a', '2', null), 1, 2),
            array('rint', array('a', '2', null), 1, 2),
            array('rfloat', array('a' => '2'), 'a', 2.0),
            array('rfloat', array('a' => 2), 'a', 2.0),
            array('rfloat', array('a' => '2.1'), 'a', 2.1),
            array('rfloat', array('a' => -1.111), 'a', -1.111),
            array('rbool', array('a' => 'x'), 'a', true),
            array('rbool', array('a' => '1'), 'a', true),
            array('rbool', array('a' => '0'), 'a', false),
            array('rbool', array('a' => 0), 'a', false),
        );
    }

    public function testSub()
    {
        $data = array(
            'a' => 1,
            'b' => array('2', 3, 4),
            'c' => array('a' => 1, 'b' => array('x' => 1, 'y' => 3)),
            'd' => null,
        );
        $conf = Config::fromArray($data);
        $this->assertTrue($conf->sub('b') === $conf->sub('b'));
        $this->assertEquals(1, $conf->int('a'));
        $this->assertEquals('2', $conf->sub('b')->str(0));
        $this->assertEquals('3', $conf->sub('c')->sub('b')->str('y'));
    }
}