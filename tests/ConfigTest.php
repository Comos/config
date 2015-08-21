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
        $this->assertTrue(Config::fromArray(['a' => 1]) instanceof Config);
        $this->assertTrue(Config::fromArray(new \ArrayObject(['a' => 1])) instanceof Config);
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
        return [
            //$method, $data, $key, $default, $expectedValue
            ['str', ['a' => '1', 'b' => 2], 'a', null, '1'],
            ['str', ['a' => '1', 'b' => 2], 'b', null, '2'],
            ['str', ['a' => '1', 'b' => 2], 'c', null, null],
            ['str', ['a' => '1', 'b' => 2], 'c', 'x', 'x'],
            ['str', ['a', 'b', 'c'], 2, null, 'c'],
            ['str', ['a', 'b', 'c'], '2', null, 'c'],
            ['int', ['a' => 1], 'a', null, 1],
            ['int', ['a' => 1.0], 'a', null, 1],
            ['int', ['a' => "1.0"], 'a', null, 1],
            ['int', ['a' => "1.0"], 'b', null, null],
            ['float', ['a' => "1.0"], 'a', null, 1.0],
            ['float', ['a' => "1.0"], 'b', 1.1, 1.1],
            ['float', ['c' => 2], 'c', 1.1, 2.0],
            ['float', ['c' => 2], 'c', 1.1, 2.0],
            ['float', ['c' => 0], 'c', 1.1, 0.0],
            ['bool', ['c' => 0], 'c', null, false],
            ['bool', ['c' => 'true'], 'c', null, true],
            ['bool', ['c' => 'False'], 'c', null, false],
        ];
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
        return [
            ['rint', ['a' => 'x'], 'a'],
            ['rint', ['a' => []], 'a'],
            ['int', ['a' => false], 'a'],
            ['int', ['a' => 'x'], 'a'],
            ['float', ['a' => []], 'a'],
            ['float', ['a' => new \stdClass()], 'a'],
            ['float', ['a' => true], 'a'],
            ['bool', ['a' => []], 'a'],
            ['rbool', ['a' => []], 'a'],
            ['bool', ['a' => new \stdClass()], 'a'],
        ];
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
        return [
            //$method, $data, $key
            ['rstr', ['a' => '1', 'b' => 2], 'c'],
            ['rstr', ['a' => '1', 'b' => 2], 'x'],
            ['rstr', ['a', 'b', 'c'], 3],
            ['rstr', ['a', 'b', null], 2],
            ['rint', ['a' => 1], 'c'],
            ['rint', ['a', 'x'], 3],
            ['rfloat', ['a', 'x'], 3],
            ['rbool', ['a', 'x'], 3],
        ];
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
        return [
            //$method, $data, $key, $expectedValue
            ['rstr', ['a' => '1', 'b' => 2], 'b', '2'],
            ['rstr', ['a' => '1', 'b' => 2], 'a', '1'],
            ['rstr', ['a', 'b', 'c'], 2, 'c'],
            ['rint', ['a', '2', null], 1, 2],
            ['rint', ['a', '2', null], 1, 2],
            ['rfloat', ['a' => '2'], 'a', 2.0],
            ['rfloat', ['a' => 2], 'a', 2.0],
            ['rfloat', ['a' => '2.1'], 'a', 2.1],
            ['rfloat', ['a' => -1.111], 'a', -1.111],
            ['rbool', ['a' => 'x'], 'a', true],
            ['rbool', ['a' => '1'], 'a', true],
            ['rbool', ['a' => '0'], 'a', false],
            ['rbool', ['a' => 0], 'a', false],
        ];
    }

    public function testSub()
    {
        $data = [
            'a' => 1,
            'b' => ['2', 3, 4],
            'c' => ['a' => 1, 'b' => ['x' => 1, 'y' => 3]],
            'd' => null,
        ];
        $conf = Config::fromArray($data);
        $this->assertTrue($conf->sub('b') === $conf->sub('b'));
        $this->assertEquals(1, $conf->int('a'));
        $this->assertEquals('2', $conf->sub('b')->str(0));
        $this->assertEquals('3', $conf->sub('c')->sub('b')->str('y'));
    }

    public function testGetKeys()
    {
        $data = [
            'a' => 1,
            'b' => ['2', 3, 4],
            'c' => ['a' => 1, 'b' => ['x' => 1, 'y' => 3]],
            'd' => null,
        ];
        $conf = Config::fromArray($data);
        $this->assertEquals(['a', 'b', 'c', 'd'], $conf->keys());
    }

    public function testGetRawData()
    {
        $data = ['a' => 1, 'b'=>['x'=>'y']];
        $conf = Config::fromArray($data);
        $this->assertEquals($data, $conf->rawData());
        $this->assertEquals(['x'=>'y'], $conf->sub('b')->rawData());
    }

}