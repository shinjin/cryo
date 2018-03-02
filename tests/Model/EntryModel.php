<?php
namespace Cryo\Test\Model;

use Cryo\Model;

class EntryModel extends Model
{
    protected static $properties = array(
        'id' => array('type' => 'integer'),
        'author' => array(
            'type' => 'object',
            'reference' => 'Cryo\Test\Model\AuthorModel'
        ),
        'content' => array('type' => 'string'),
        'created' => array('type' => 'string')
    );

    protected static $table = 'guestbook';
}
