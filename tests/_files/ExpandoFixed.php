<?php
namespace Cryo\Test\_files;

use Cryo\Model\Expando;

class ExpandoFixed extends Expando
{
    protected static $properties = array(
        'id' => array('type' => 'integer'),
        'author' => array(
            'type' => 'object',
            'reference' => 'Cryo\Test\_files\Author'
        ),
        'content' => array('type' => 'string'),
        'created' => array('type' => 'string')
    );

    protected static $table = 'entry';
}
