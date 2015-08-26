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
     * @var Config[]
     */
    protected $subs;

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

    public function sub($key)
    {
        if (!isset($this->subs[$key])) {
            $this->subs[$key] = $this->genSub($key);
        }
        return $this->subs[$key];
    }

    protected function genSub($key)
    {
        if (!\array_key_exists($key, $this->data)) {
            return self::fromArray(array());
        }
        $arr = $this->data[$key];
        if (is_array($arr)) {
            return self::fromArray($arr);
        }
        if (is_object($arr)) {
            return self::fromArray((array)$arr);
        }
        throw new Exception('type error, expects array or object, field: ' . $key);
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return null|string
     * @throws Exception
     */
    public function str($key, $default = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $default;
        }
        $value = $this->data[$key];
        if (is_string($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return strval($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return strval($value);
        }
        throw new Exception('type error, field: ' . $key);
    }

    public function int($key, $default = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $default;
        }
        $value = $this->data[$key];
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return intval($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return intval($value);
        }

        throw new Exception('type error, field: ' . $key);
    }

    public function float($key, $default = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $default;
        }
        $value = $this->data[$key];
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return floatval($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return floatval($value);
        }

        throw new Exception('type error, field: ' . $key);
    }

    public function bool($key, $default = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $default;
        }
        $value = $this->data[$key];
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return boolval($value);
        }

        if (is_string($value)) {
            if (strlen($value) == 5 && strtolower($value) == 'false') {
                return false;
            }
            return boolval($value);
        }

        throw new Exception('type error, field: ' . $key);
    }

    /**
     * get string field value in restrict mode.
     * @param mixed $key
     * @throws Exception
     * @return string
     */
    public function rstr($key)
    {
        return $this->rvalue($key, 'str');
    }

    /**
     * @param $key
     * @return int|null
     * @throws Exception
     */
    public function rint($key)
    {
        return $this->rvalue($key, 'int');
    }

    /**
     * @param $key
     * @return float|null
     * @throws Exception
     */
    public function rfloat($key)
    {
        return $this->rvalue($key, 'float');
    }

    /**
     * @param $key
     * @return boolean|null
     * @throws Exception
     */
    public function rbool($key)
    {
        return $this->rvalue($key, 'bool');
    }

    /**
     * @param $key
     * @param $typeMethod
     * @return mixed
     * @throws Exception
     */
    protected function rvalue($key, $typeMethod)
    {
        $value = $this->$typeMethod($key);
        if (is_null($value)) {
            throw new Exception('miss required field: ' . $key);
        }
        return $value;
    }

}