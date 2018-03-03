<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class Entry extends Model
{
    protected static $properties = array(
        'id' => array('type' => 'integer'),
        'author' => array(
            'type' => 'object',
            'reference' => 'Cryo\Test\Model\Author'
        ),
        'content' => array('type' => 'string'),
        'created' => array('type' => 'string')
    );
}
