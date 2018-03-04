<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class Author extends Model
{
    protected static $properties = array(
        'id'   => array('type' => 'integer'),
        'name' => array('type' => 'string')
    );

    protected static $table = 'author';
}
