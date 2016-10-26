<?php

namespace Balance\Core\Model\Data;

/**
 * Interface DataInterface
 *
 * @package Balance\Core\Model\Data
 * @author Derek Li
 */
interface DataInterface
{
    /**
     * Set the data in array.
     *
     * @param array $data
     * @return $this
     */
    public function set(array $data);

    /**
     * Get the data in array.
     *
     * @return array
     */
    public function get();
}