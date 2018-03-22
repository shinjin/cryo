<?php
namespace Cryo\Test\_files;

use Cryo\Model;

class Author extends Model
{
    protected static $table = 'author';

    protected static $id   = array('type' => 'integer');
    protected static $name = array('type' => 'string');
}
