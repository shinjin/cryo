<?php
namespace Cryo\Model;

use Cryo\Key;
use Cryo\Model;
use Cryo\Freezer\Storage\PolyModel as PolyModelStorage;
use Freezer\Freezer;
use Freezer\Storage;

class PolyModel extends Model
{
    /**
     * @inherits
     */
    protected static $storage;

    /**
     * @inherits
     */
    protected static function createStorage(): Storage
    {
        $freezer = new Freezer('__key', self::getPropertyReader());
        return new PolyModelStorage(self::getDb(), $freezer);
    }
}
