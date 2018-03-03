<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class EntryArray extends Model
{
    protected static $properties = array(
        'id' => array('type' => 'integer'),
        'author' => array(
            'type' => 'array',
            'reference' => 'Cryo\Test\Model\Author'
        ),
        'content' => array('type' => 'string'),
        'created' => array('type' => 'string')
    );

    protected static $table = 'entry';
}
