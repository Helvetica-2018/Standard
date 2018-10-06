<?php
namespace Helvetica\Standard;

use Psr\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Helvetica\Standard\Exception\NotFoundException;
use Helvetica\Standard\Exception\ContainerOverrideException;
use Helvetica\Standard\Exception\UnknownIdentifierException;

class Container implements ContainerInterface, \ArrayAccess
{
    public $_store;
    public $_frozen;
    public function __construct()
    {
        $this->_store = [];
        $this->_frozen = [];
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->offsetExists($id);
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
     * @throws UnknownIdentifierException  No entry was found for **this** identifier.
     * 
     * @return mixed Entry
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
     * @param string|integer $offset Entry id
     * @param mixed $value Entry
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
     * @param string|integer $offset Entry id
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
     * @return array An array of value names
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

    /**
     * @param string|integer $id
     * 
     * @throws UnknownIdentifierException  No entry was found for **this** identifier.
     * 
     * @return mixed Entry
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * Assign a value to the specified entry id.
     * 
     * @param string|integer $offset Entry id
     * @param mixed $value Entry
     * 
     * @throws ContainerExceptionInterface When entry is exists.
     */
    public function __set($id, $callable)
    {
        $this->offsetSet($id, $callable);
    }
}
