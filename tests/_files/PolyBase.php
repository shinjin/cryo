<?php
namespace Cryo\Test\_files;

use Cryo\Model\PolyModel;

class PolyBase extends PolyModel
{
    protected static $table = 'poly_base';

    protected static $id = array('type' => 'integer');
}
