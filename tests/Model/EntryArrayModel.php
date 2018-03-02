<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class EntryArrayModel extends Model
{
    protected static $properties = array(
        'id' => array('type' => 'integer'),
        'author' => array(
            'type' => 'array',
            'reference' => 'Cryo\Test\Model\AuthorModel'
        ),
        'content' => array('type' => 'string'),
        'created' => array('type' => 'string')
    );

    protected static $table = 'guestbook';
}
