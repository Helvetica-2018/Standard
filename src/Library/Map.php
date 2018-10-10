<?php
namespace Helvetica\Standard\Library;

class Map
{
    /** @var array */
    protected $storage;

    public static function normalizeKey($key)
    {
        return strtr(strtolower($key), '_', '-');
    }

    /**
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->storage = [];
        foreach ($data as $key => $value) {
            $this->storage[static::normalizeKey($key)] = $value;
        }
    }

    /**
     * Is normalize key exists?
     * 
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $normalizeKey = static::normalizeKey($key);
        return \array_key_exists($normalizeKey, $this->storage);
    }

    /**
     * Get a storaged value.
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $normalizeKey = static::normalizeKey($key);
        if (\array_key_exists($normalizeKey, $this->storage)) {
            return $this->storage[$normalizeKey];
        }
        return $default;
    }

    /**
     * Set a value.
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $normalizeKey = static::normalizeKey($key);
        $this->storage[$normalizeKey] = $value;
    }

    /**
     * Get all data
     */
    public function all()
    {
        return $this->storage;
    }

    /**
     * Count data
     */
    public function count()
    {
        return count($this->all());
    }
}
