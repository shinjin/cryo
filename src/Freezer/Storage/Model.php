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
     * @throws InvalidArgumentException
     */
    public function __construct(
        Db $db,
        Freezer $freezer = null,
        $useLazyLoad = false
    ){
        parent::__construct($freezer, $useLazyLoad);

        $this->db = $db;
        $this->keys = array();
        $this->blacklist = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function doStore(array $frozenObject)
    {
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

                    $values = $this->makeValuesForDb(
                        $object['class'],
                        $object['state']
                    );

                    $this->db->insert($table, $values, array_keys($id));

                    if ($isAutoIncrementId) {
                        $this->keys[$encodedKey] = (int)$this->db->lastInsertId();
                        $key->setId($this->keys[$encodedKey]);
                    } else {
                        $this->keys[$encodedKey] = current($id);
                    }
                }
            }
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($encodedKey, array &$objects = array())
    {
        $isRoot = empty($objects);

        if (!isset($objects[$encodedKey])) {
            $key   = new Key($encodedKey);
            $class = $key->getClass();

            $sth = $this->db->query(
                $this->buildQueryStatement($key, $class),
                $key->getId()
            );

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

    protected function buildQueryStatement(Key $key, string $class): string
    {
        $filter = $this->db->buildQueryFilter($key->getIdPair());
        return sprintf('SELECT * FROM %s WHERE %s', $class::getTable(), $filter);
    }

    private function makeValuesForDb(string $class, array $data): array
    {
        $values = array();

        foreach($class::getProperties($this->blacklist) as $name => $property) {
            $values[$name] = $property->makeValueForDb($data[$name], $this->keys);
        }

        return $values;
    }

    private function makeValuesFromDb(string $class, array $data): array
    {
        $values = array();

        foreach($class::getProperties() as $name => $property) {
            $values[$name] = $property->makeValueFromDb($data[$name]);
        }

        return $values;
    }
}
