<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * 
 */
interface ValueInterface
{
    /**
     * Get item Value.
     * 
     * @return array
     */
    public function getValue();

    /**
     * Set item Value. Overwrites properties if already set.
     * 
     * @param array $values
     */
    public function setValue(array $values);

    /**
     * Recursively merge one item Value into another.
     * 
     * @param ValueInterface|Array $object
     */
    public function mergeValue($object);

    /**
     * Sets all public properties to null.
     * 
     * @param array $value
     */
    public function resetValue();
}
