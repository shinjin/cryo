<?php
namespace Cryo\Freezer\Storage;

use Cryo\Key;
use Freezer\Freezer;
use Freezer\Exception\InvalidArgumentException;
use Freezer\Storage\Pdo;
use Shinjin\Pdo\Db;

class Cryo extends Pdo
{
    /**
     * @inheritdoc
     */
    public function __construct(
        $db,
        Freezer $freezer = null,
        $useLazyLoad = false,
        $table = 'cryo',
        array $db_options = array()
    ){
        parent::__construct($db, $freezer, $useLazyLoad, $table, $db_options);
    }

    /**
     * @inheritdoc
     */
    protected function doStore(array $frozenObject)
    {
        // reverse order of objects
        $objects = array_reverse($frozenObject['objects']);

        // define old/new key mapping
        $keys = array();

        foreach ($objects as $key => $object) {
            if ($object['isDirty'] === true) {
                // extract id from object
                $pk = $object['class']::getPrimaryKey();
                $id = array_intersect_key($object['state'], array_flip($pk));
                $isAutoIncrementId = count($id) === 1 && current($id) === null;

                $table = $object['class']::getTable();

                // loop through state and format values for db
                $values = $this->makeValuesForDb(
                    $object['class'],
                    $object['state'],
                    $keys
                );

                // if key is set try update
                if (!$isAutoIncrementId) {
                    $updates = $this->db->update($table, $values, $id);
                    $keys[$key] = current($id);
                }

                // if record not updated try insert
                if (empty($updates)) {
                    if ($isAutoIncrementId) {
                       $values = array_diff_key($values, $id);
                    }

                    $this->db->insert($table, $values);

                    // if autoincrement id, add to mapping
                    if ($isAutoIncrementId) {
                        $keys[$key] = (integer)$this->db->lastInsertId();
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($encodedKey, array &$objects = array())
    {
        $isRoot = empty($objects);

        if (!isset($objects[$encodedKey])) {
            $key   = new Key($encodedKey);
            $class = $key->getClass();
            $table = $class::getTable();

            $filter = $this->db->buildQueryFilter($key->getIdPair());
            $stmt = sprintf('SELECT * FROM %s WHERE %s', $table, $filter);
            $stmt = $this->db->query($stmt, $key->getId());

            if (($result = $stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $object = array(
                    'class'   => $class,
                    'isDirty' => false,
                    'state'   => array(
                        'state' => $this->makeValuesFromDb($class, $result)
                    )
                );
            } else {
                return false;
            }

            $objects[$encodedKey] = $object;

            if (!$this->useLazyLoad) {
                $this->fetchArray($object['state'], $objects);
            }
        }

        if ($isRoot) {
            return array('root' => $encodedKey, 'objects' => $objects);
        }
    }

    private function makeValuesForDb($class, array $data, array $keys)
    {
        $values = array();

        foreach($class::getProperties() as $name => $property) {
            $values[$name] = $property->makeValueForDb($data[$name], $keys);
        }

        return $values;
    }

    private function makeValuesFromDb($class, array $data)
    {
        $values = array();

        foreach($class::getProperties() as $name => $property) {
            $values[$name] = $property->makeValueFromDb($data[$name]);
        }

        return $values;
    }
}
