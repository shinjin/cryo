<?php
namespace Cryo\Model;

use Cryo\Model;
use Cryo\Freezer\Storage\Cryo;
use Freezer\Freezer;
use Freezer\Storage;
use Freezer\Storage\ChainStorage;
use Freezer\Storage\Pdo;

class Expando extends Model
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
        return new ChainStorage(
            array(
                new  Pdo(self::getDb(), $freezer, false, 'cryo'),
                new Cryo(self::getDb(), $freezer)
            ),
            $freezer
        );
    }

}
