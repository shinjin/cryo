<?php
namespace Cryo\Model;

use Cryo\Model;
use Cryo\Freezer\Storage\Cryo;
use Cryo\Freezer\Storage\Pdo;
use Freezer\Freezer;
use Freezer\Storage;
use Freezer\Storage\ChainStorage;

class Expando extends Model
{
    /**
     * @inherits
     */
    protected static $storage;

    /**
     * @inherits
     */
    public function __set(string $name, $value): void
    {
        if (!array_key_exists($name, static::$properties)) {
            $this->state[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

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
