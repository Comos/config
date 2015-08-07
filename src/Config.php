<?php
/**
 * Created by PhpStorm.
 * User: zhaoqing2
 * Date: 15/8/6
 * Time: 下午4:32
 */

namespace Comos\Config;

class Config
{
    /**
     *
     * @var array
     */
    protected $data;

    /**
     * @param array|\ArrayAccess $data
     * @return Config
     * @throws \InvalidArgumentException
     */
    public static function fromArray($data)
    {
        if (!is_array($data) && !$data instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('the argument must be array or ArrayAccess');
        }
        return new self($data);
    }

    /**
     *
     * @param array $data
     */
    protected function __construct($data)
    {
        $this->data = $data;
    }

    /**
     *
     * @param mix $key
     * @param string $default
     * @return string|null
     */
    public function str($key, $default = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $default;
        }

        return strval($this->data[$key]);
    }

    /**
     * get string field value in restrict mode.
     * @param mix $key
     * @throws Exception
     * @return string
     */
    public function rstr($key)
    {
        $value = $this->str($key);
        if (is_null($value)) {
            throw new Exception('miss required field: ' . $key);
        }
        return $value;
    }
}