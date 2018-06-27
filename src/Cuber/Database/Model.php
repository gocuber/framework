<?php

/**
 * Model
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use Cuber\Database\DB;

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
