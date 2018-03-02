<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class BadModel extends Model
{
    protected static $properties = array(
        'id'   => array('type' => 'integer'),
        'name' => array('type' => 'invalid')
    );

    protected static $table = 'bad';
}
