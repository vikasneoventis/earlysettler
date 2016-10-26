<?php

namespace Balance\Core\Model\Data;

/**
 * Class DataAbstract
 *
 * @package Balance\Core\Model\Data
 * @author Derek Li
 */
abstract class DataAbstract implements DataInterface
{
    /**
     * If the attribute has to be set via setter function.
     *
     * @var bool
     */
    protected $strict = true;

    /**
     * The data container.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Set each attribute via its own set method.
     * e.g.
     * first_name => setFirstName
     *
     * @param array $data
     * @return $this
     */
    public function set(array $data)
    {
        foreach ($data as $attr => $val) {
            $setMethod = sprintf('set%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $attr))));
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($val);
            } elseif (!$this->strict) {
                $this->data[$attr] = $val;
            }
        }
        return $this;
    }

    /**
     * Get the data in array.
     *
     * @return array
     */
    public function get()
    {
        return $this->data;
    }
}