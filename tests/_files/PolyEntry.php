<?php
namespace Cryo\Test\_files;

class PolyEntry extends PolyBase
{
    protected static $table = 'poly_entry';

    protected static $author = array(
        'type' => 'object',
        'reference' => 'Cryo\Test\_files\Author'
    );
    protected static $content = array('type' => 'string');
}
