<?php
namespace Cryo\Model;

use Cryo\Model;
use Cryo\Exception\NotSavedException;
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
    public function delete(): int
    {
        if (!$this->isSaved()) {
            throw new NotSavedException('Object has not been saved.');
        }

        $affected_rows = 0;
        $id = $this->state['__key']->getIdPair();

        self::getDb()->beginTransaction();

        try {
            foreach(self::getClassHierarchy() as $class) {
                $affected_rows += self::getDb()->delete($class::getTable(), $id);
            }

            self::getDb()->commit();
        } catch (\PDOException $e) {
            self::getDb()->rollBack();
            throw new \PDOException;
        }

        return $affected_rows;
    }

    /**
     * @inherits
     */
    protected static function createStorage(): Storage
    {
        $freezer = new Freezer('__key', self::getPropertyReader());
        return new PolyModelStorage(self::getDb(), $freezer);
    }
}
