<?php
namespace Cryo\Test\_files;

use Cryo\Model;

class Entry extends Model
{
    protected static $table = 'entry';

    protected static $id = array('type' => 'integer');
    protected static $author = array(
        'type' => 'object',
        'reference' => 'Cryo\Test\_files\Author'
    );
    protected static $content = array('type' => 'string');
    protected static $created = array('type' => 'string');
}
