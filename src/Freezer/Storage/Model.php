<?php
namespace Cryo\Freezer\Storage;

use Cryo\Key;
use Freezer\Freezer;
use Freezer\Storage;
use Freezer\Exception\InvalidArgumentException;
use Shinjin\Pdo\Db;

class Model extends Storage
{
    /**
     * @var Shinjin\Pdo\Db
     */
    protected $db;

    /**
     * @var Mapping of old/new keys
     */
    protected $keys;

    /**
     * @var List of properties to blacklist on storage
     */
    protected $blacklist;

    /**
     * Constructor.
     *
     * @param  \PDO|array      $pdo         PDO object or array of db parameters
     * @param  Freezer\Freezer $freezer     Freezer instance to be used
     * @param  boolean         $useLazyLoad Flag that controls whether objects
     *                                      are fetched using lazy load or not
     * @param  array           $db_options  PDO options
     * @throws InvalidArgumentException
     */
    public function __construct(
        Db $db,
        Freezer $freezer = null,
        $useLazyLoad = false,
        array $db_options = array()
    ){
        parent::__construct($freezer, $useLazyLoad);

        $this->db = $db;
        $this->keys = array();
        $this->blacklist = array();
    }

    /**
     * @inheritdoc
     */
    protected function doStore(array $frozenObject)
    {
        // reverse order of objects
        $objects = array_reverse($frozenObject['objects']);

        foreach ($objects as $encodedKey => $object) {
            if ($object['isDirty'] === true) {
                $key = new Key($encodedKey);
                $table = $object['class']::getTable();

                if (!empty($table)) {
                    // extract id from object
                    $pk = $object['class']::getPrimaryKey();
                    $id = array_intersect_key($object['state'], array_flip($pk));
                    $isAutoIncrementId = count($id) === 1 && current($id) === null;

                    // loop through state and format values for db
                    $values = $this->makeValuesForDb(
                        $object['class'],
                        $object['state']
                    );

                    // if key is set try update
                    if (!$isAutoIncrementId) {
                        $updates = $this->db->update($table, $values, $id);
                        $this->keys[$encodedKey] = current($id);
                    }

                    // if record not updated try insert
                    if (empty($updates)) {
                        if ($isAutoIncrementId) {
                           $values = array_diff_key($values, $id);
                        }

                        $this->db->insert($table, $values);

                        // if autoincrement id, add to mapping
                        if ($isAutoIncrementId) {
                            $this->keys[$encodedKey] = $this->db->lastInsertId();
                            $key->setId($this->keys[$encodedKey]);
                        }
                    }
                }
            }
        }

        return $key;
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

            $statement = $this->buildQueryStatement($key, $class);
            $sth = $this->db->query($statement, $key->getId());

            if (($result = $sth->fetch(\PDO::FETCH_ASSOC)) !== false) {
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

    private function buildQueryStatement(Key $key, $class)
    {
        $filter = $this->db->buildQueryFilter($key->getIdPair());
        return sprintf('SELECT * FROM %s WHERE %s', $class::getTable(), $filter);
    }

    private function makeValuesForDb($class, array $data)
    {
        $values = array();

        foreach($class::getProperties($this->blacklist) as $name => $property) {
            $values[$name] = $property->makeValueForDb($data[$name], $this->keys);
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
