<?php
namespace Cryo;

use Cryo\Exception\BadArgumentException;
use Cryo\Exception\NotSavedException;
use Cryo\Exception\PropertyException;
use Cryo\Freezer\Storage\Model as ModelStorage;

use Freezer\Freezer;
use Freezer\Storage;
use Shinjin\Pdo\Db;

abstract class Model
{
    /**
     * Db object.
     *
     * @var \Shinjin\Pdo\Db
     */
    protected static $db;

    /**
     * Freezer storage object.
     *
     * @var \Cryo\Freezer\Storage\Model
     */
    protected static $storage;

    /**
     * Db table name.
     *
     * @var string
     */
    protected static $table;

    /**
     * Db table's primary key. Can be string or array of column names for
     * composite keys.
     *
     * @var string|array
     */
    protected static $primary_key = 'id';

    /**
     * List of object properties.
     *
     * @var array
     */
    protected static $properties;


    /**
     * A list of properties to only dump or only load.
     *
     * @var array
     */
    protected static $only = array(
        'dump' => array('__freezer', '__key'),
        'load' => array('__freezer', '__key')
    );

    /**
     * Internal property that contains Freezer data.
     *
     * @var array
     */
    protected static $__freezer = array('type' => 'array');

    /**
     * Internal property that contains object key.
     *
     * @var array
     */
    protected static $__key = array('type' => 'key');

    /**
     * The object state.
     *
     * @var array
     */
    protected $state;

    /**
     * Constructor
     *
     * @param array $state List of property values to assign to the object.
     */
    public function __construct(array $state = array())
    {
        self::initializeProperties();

        $this->state = array(
            '__key' => Key::generate(get_class($this), uniqid())
        );

        $this->load($state);
    }

    /**
     * Returns the object property value.
     *
     * @param string $name Property name
     *
     * @return mixed
     * @throws \Cryo\Exception\PropertyException
     */
    public function &__get(string $name)
    {
        if (!array_key_exists($name, $this->state)) {
            if (!property_exists($this, $name)) {
                throw new PropertyException(
                    sprintf('Property "%s" does not exist.', $name)
                );                
            }

            $this->state[$name] = static::$$name->getDefaultValue();
        }

        return $this->state[$name];
    }

    /**
     * Validates and sets the object property value.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return void
     * @throws \Cryo\Exception\PropertyException
     */
    public function __set(string $name, $value): void
    {
        if (!property_exists($this, $name)) {
            throw new PropertyException(
                sprintf('Property "%s" does not exist.', $name)
            );
        }

        $this->state[$name] = static::$$name->validate($value);

        $primary_key = self::getPrimaryKey();

        if (in_array($name, $primary_key) && !empty($value)) {
            $id = array_intersect_key($this->state, array_flip($primary_key));
            $this->state['__key']->setId($id);
        }
    }

    /**
     * Checks whether the property is set.
     *
     * @param string $name Property to check
     *
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return isset($this->state[$name]);
    }

    /**
     * Converts the object properties to JSON and returns it.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Returns the db object.
     *
     * @return Shinjin\Pdo\Db
     */
    public static function getDb(): Db
    {
        if (empty(self::$db)) {
            self::$db = self::createDb();
        }

        return self::$db;
    }

    /**
     * Sets the db object.
     *
     * @return void
     */
    public static function setDb(Db $db): void
    {
        self::$db = $db;
    }

    /**
     * Returns the Freezer storage object.
     *
     * @return Freezer\Storage
     */
    public static function getStorage(): Storage
    {
        if (empty(static::$storage)) {
            static::$storage = static::createStorage();
        }

        return static::$storage;
    }

    /**
     * Returns the db table name.
     *
     * @return string|null
     */
    public static function getTable(): ?string
    {
        return static::$table;
    }

    /**
     * Returns the db table's primary key.
     *
     * @return array
     */
    public static function getPrimaryKey(): array
    {
        $pk = (array)static::$primary_key;
        sort($pk);
        return $pk;
    }

    /**
     * Initializes the properties if necessary and returns them.
     *
     * @return array
     */
    public static function getProperties(array $blacklist = array()): array
    {
        $class = get_called_class();

        if (empty(self::$properties[$class])) {
            self::initializeProperties();
        }

        $properties = array_intersect_key(
            self::$properties[$class],
            get_class_vars($class)
        );

        array_push($blacklist, '__key');
        return array_diff_key($properties, array_flip($blacklist));        
    }

    /**
     * Returns the custom object property reader for Freezer.
     *
     * @return callable
     */
    public static function getPropertyReader()
    {
        return function($object) {
            $result = array();
            foreach ($object->getProperties() as $name => $property) {
                // ignore all properties that start with an underscore
                if (strpos($name, '_') !== 0) {
                    $result[$name] = $object->{$name};
                }
            }
            // except __freezer
            $result['__freezer'] = $object->__freezer ?? null;
            return $result;
        };
    }

    /**
     * Returns class hierarchary.
     *
     * @return callable
     */
    public static function getClassHierarchy(): array
    {
        $class   = get_called_class();
        $classes = array_reverse(
            array_values(
                array_diff_key(
                    class_parents($class, false),
                    array_flip(array('Cryo\\Model', 'Cryo\\Model\\PolyModel'))
                )
            )
        );
        array_push($classes, $class);

        return $classes;
    }

    /**
     * Fetches an object or list of objects by id(s).
     *
     * @param string|integer|array $ids The object id or list of ids.
     *
     * @return \Cryo\Model|array
     */
    public static function get($ids)
    {
        if (!is_array($ids) ||
            count(self::getPrimaryKey()) >= 2 &&
            count($ids) === count($ids, COUNT_RECURSIVE)
        ) {
            $ids = array($ids);
        }

        $class = get_called_class();
        $objects = array();
        $storage = self::getStorage();

        foreach($ids as $id) {
            $key = Key::generate($class, $id);
            array_push($objects, $storage->fetch((string)$key));
        }

        return count($ids) === 1 ? $objects[0] : $objects;
    }

    /**
     * Fetches an object or list of objects by key(s).
     *
     * @param string|array $keys The object key or list of keys.
     *
     * @return \Cryo\Model|array
     */
    public static function getByKey($keys)
    {
        if (!is_array($keys)) {
            $keys = array($keys);
        }

        $objects = array();
        $storage = self::getStorage();

        foreach($keys as $key) {
            array_push($objects, $storage->fetch((string)$key));
        }

        return count($keys) === 1 ? $objects[0] : $objects;
    }

    /**
     * Returns the object's key.
     *
     * @return \Cryo\Key
     */
    public function getKey(): Key
    {
        if (!$this->isSaved()) {
            throw new NotSavedException(
                'Key does not exist because the object has not been saved.'
            );
        }

        return $this->state['__key'];
    }

    /**
     * Saves the object.
     *
     * @return \Cryo\Key
     */
    public function put(): Key
    {
        $key = new Key(self::getStorage()->store($this));

        // update object id
        $this->state = array_intersect_key($key->getIdPair(), $this->state) +
                       $this->state;

        return $key;
    }

    /**
     * Deletes the object.
     *
     * @return integer The affected row count.
     */
    public function delete(): int
    {
        if (!$this->isSaved()) {
            throw new NotSavedException('Object has not been saved.');
        }

        $id = $this->state['__key']->getIdPair();
        return self::getDb()->delete(static::$table, $id);
    }

    /**
     * Filters the property values and returns them.
     *
     * @return array The list of property name/values.
     */
    public function dump(): array
    {
        $state = array_diff_key($this->state, array_flip(static::$only['load']));
        return $this->toArray($state);
    }

    /**
     * Filters the input data and assigns them to the object.
     *
     * @param  array $state The list of property values to assign to the object.
     *
     * @return void
     */
    public function load(array $state, bool $strict = true): void
    {
        foreach($state as $name => $value)
        {
            $this->__set($name, $value);
        }
    }

    /**
     * Checks whether the object has been changed.
     *
     * @return boolean True if the object has been modified, otherwise false.
     */
    public function isDirty(): bool
    {
        if ($this->isSaved()) {
            if (isset($this->state['__freezer']['hash'])) {
                $hash = self::getStorage()->getFreezer()->generateHash($this);

                if ($this->state['__freezer']['hash'] === $hash) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether the object has been saved to the db.
     *
     * @return boolean True if the object has been saved, otherwise false.
     */
    public function isSaved(): bool
    {
        if (empty($this->state['__freezer'])) {
            return false;
        }

        return true;
    }

    /**
     * Converts the object properties to JSON and returns it.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->dump());
    }

    /**
     * Creates a new Db object.
     *
     * @return \Shinjin\Pdo\Db
     */
    private static function createDb(): Db
    {
        return new Db(
            array(
                'driver'   => getenv('DB_DRIVER'),
                'dbname'   => getenv('DB_DBNAME'),
                'host'     => getenv('DB_HOST'),
                'port'     => getenv('DB_PORT'),
                'user'     => getenv('DB_USER'),
                'password' => getenv('DB_PASSWORD'),
                'dsn'      => getenv('DB_DSN')
            )
        );
    }

    /**
     * Creates a new Freezer storage object.
     *
     * @return \Freezer\Storage
     */
    private static function createStorage(): Storage
    {
        $freezer = new Freezer('__key', self::getPropertyReader());
        return new ModelStorage(self::getDb(), $freezer);
    }

    /**
     * Creates a new property for the object.
     *
     * @return \Cryo\Property
     * @throws \Cryo\Exception\PropertyException
     */
    private static function createProperty(string $name, array $params): Property
    {
        $params = array_replace(Property::DEFAULT_PARAMS, $params);
        $class  = sprintf('\\Cryo\\Property\\%sProperty', ucfirst($params['type']));

        if (!class_exists($class)) {
            throw new PropertyException(
                sprintf('%s property is not defined.', ucfirst($params['type']))
            );
        }

        return new $class($name, $params);
    }

    /**
     * Converts the property parameter array to property objects.
     *
     * @return void
     */
    private static function initializeProperties(): void
    {
        $called_class = get_called_class();

        $properties = array();
        foreach(self::getClassHierarchy() as $class) {
            $properties = array_merge($properties, get_class_vars($class));
        }

        self::$properties[$called_class] = array_merge(
            array_diff_key($properties, get_class_vars('\\Cryo\\Model')),
            array('__freezer' => self::$__freezer, '__key' => self::$__key)
        );

        foreach(self::$properties[$called_class] as $name => &$property) {
            if (is_array($property)) {
                $property = self::createProperty($name, $property);
                static::$$name = $property;

                $only = $property->getOnly();
                if (!empty($only) && !in_array($name, static::$only[$only])) {
                    array_push(static::$only[$only], $name);
                }
            }
        }
    }

    /**
     * Traverses array properties and JSON encodes the object elements.
     *
     * @param array $array The property array to traverse.
     *
     * @return array
     */
    private function toArray(array $array): array
    {
        foreach($array as &$value) {
            if (is_array($value)) {
                $value = $this->toArray($value);
            } elseif(is_object($value)) {
                $value = $value->dump();
            }
        }

        return $array;
    }
}
