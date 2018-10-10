<?php
namespace Helvetica\Standard;

use Psr\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Helvetica\Standard\Exception\ContainerOverrideException;
use Helvetica\Standard\Exception\UnknownIdentifierException;

class Container implements ContainerInterface, \ArrayAccess
{
    public $_store = [];
    public $_frozenn = [];

    /**
     * Get an entry of the container.
     *
     * @param string $id
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Assign a value.
     * 
     * @param string|integer $offset
     * @param mixed $value
     * 
     * @throws ContainerOverrideException When entry is exists.
     */
    public function set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * Merge values to store.
     * 
     * @param array $array
     */
    public function merge($array)
    {
        $this->_store = \array_merge($this->_store, $array);
    }

    /**
     * Return true whether an container id exists.
     * Return false otherwise.
     * 
     * @param string|integer $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_store);
    }

    /**
     * Retrieve an entry to return.
     * 
     * @param string|integer $offset
     * 
     * @throws UnknownIdentifierException
     * 
     * @return mixed
     */

    public function offsetGet($offset)
    {
        if (! $this->has($offset)) {
            throw new UnknownIdentifierException($offset);
        }
        if (!isset($this->_frozen[$offset])) {
            $entry = &$this->_store[$offset];
            if (is_callable($entry)) {
                $entry = $entry($this);
            }
            $this->_frozen[$offset] = &$entry;
        }
        return $this->_frozen[$offset];
    }

    /**
     * Assign a value to the specified entry id.
     * 
     * @param string|integer $offset
     * @param mixed $value
     * 
     * @throws ContainerOverrideException When entry is exists.
     */
    public function offsetSet($offset, $value)
    {
        if ($this->has($offset)) {
            throw new ContainerOverrideException('This entry is exists.');
        }
        $this->_store[$offset] = $value;
    }

    /**
     * Unset an Entry
     * 
     * @param string|integer $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->has($offset)) {
            unset(
                $this->_store[$offset],
                $this->_frozen[$offset]
            );
        }
    }

    /**
     * Clear store and frozen entrys
     */
    public function clear()
    {
        foreach ($this->keys() as $key) {
            unset($this->_store[$key]);
        }
        $frozenKeys = \array_keys($this->_frozen);
        foreach ($frozenKeys as $key) {
            unset($this->_frozen[$key]);
        }
    }

    /**
     * Returns all defined value names.
     *
     * @return array
     */
    public function keys()
    {
        return \array_keys($this->_store);
    }

    /**
     * Count store
     */
    public function count()
    {
        return count($this->_store);
    }
}
