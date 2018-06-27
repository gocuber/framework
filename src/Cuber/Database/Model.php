<?php

/**
 * Model
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Model extends DB
{

    protected $_key = 'default';

    protected $_dbname = '';

    protected $_name = '';

    protected $_primarykey = 'id';

    protected $_fields = [];

    /**
     * getFields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

}
