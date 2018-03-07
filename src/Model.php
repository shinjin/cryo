<?php
namespace Cryo;

use Cryo\Exception\InvalidArgumentException;
use Cryo\Exception\NotSavedException;
use Cryo\Freezer\Storage\Cryo;

use Freezer\Freezer;
use Freezer\Storage;
use Shinjin\Pdo\Db;

abstract class Model
{
    /**
     * The db object.
     *
     * @var \Shinjin\Pdo\Db
     */
    protected static $db;

    /**
     * The Freezer storage object.
     *
     * @var \Cryo\Freezer\Storage\Cryo
     */
    protected static $storage;

    /**
     * The db table name.
     *
     * @var string
     */
    protected static $table;

    /**
     * The db table's primary key. Can be string or array of column names for
     * composite keys.
     *
     * @var string|array
     */
    protected static $primary_key = 'id';

    /**
     * A list that defines the model properties and parameters.
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
        'dump' => array(),
        'load' => array('__freezer', '__key')
    );

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
        if (!self::$db instanceof Db) {
            throw new InvalidArgumentException('Db and storage are not defined.');
        }

        $this->state = array('__key' => Key::generate(get_class($this)));
        $this->load($state);
    }

    /**
     * Returns the object property value.
     *
     * @param string $name Property name
     *
     * @return mixed
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function &__get(string $name)
    {
        if (!array_key_exists($name, static::$properties)) {
            throw new InvalidArgumentException(
                sprintf('Property "%s" does not exist.', $name)
            );
        }

        return $this->state[$name];
    }

    /**
     * Validates and sets the object property value.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return mixed
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function __set(string $name, $value): void
    {
        if (!array_key_exists($name, static::$properties)) {
            throw new InvalidArgumentException(
                sprintf('Property "%s" does not exist.', $name)
            );
        }

        $this->state[$name] = static::$properties[$name]->validate($value);

        $primary_key = self::getPrimaryKey();

        if (in_array($name, $primary_key)) {
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
     * Returns the db table name.
     *
     * @return string
     */
    public static function getTable(): string
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
        return (array)static::$primary_key;
    }

    /**
     * Initializes the properties if necessary and returns them.
     *
     * @return array
     */
    public static function getProperties($blacklist = array()): array
    {
        if (!current(static::$properties) instanceof Property) {
            self::initializeProperties();
        }

        array_push($blacklist, '__key');
        return array_diff_key(static::$properties, array_flip($blacklist));
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
            $result['__freezer'] = $object->__freezer;
            return $result;
        };
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

        foreach($ids as $id) {
            $key = Key::generate($class, $id);
            array_push($objects, self::$storage->fetch((string)$key));
        }

        return count($objects) === 1 ? $objects[0] : $objects;
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

        foreach($keys as $key) {
            array_push($objects, self::$storage->fetch((string)$key));
        }

        return count($objects) === 1 ? $objects[0] : $objects;
    }

    /**
     * Creates the db and Freezer storage objects.
     *
     * @param \Pdo|array $pdo The PDO object or array of db parameters.
     */
    public static function initializeDb($pdo): void
    {
        try {
            self::$db = new Db($pdo);
        } catch(\Shinjin\Pdo\Exception\InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                'initializeDb arg must be a pdo object or array.'
            );
        }

        $freezer = new Freezer('__key', self::getPropertyReader());

        self::$storage = new Cryo(self::$db, $freezer);
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
     */
    public function put(): void
    {
        self::$storage->store($this);
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

        return self::$db->delete(static::$table, $this->state['__key']->getIdPair());
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
     */
    public function load(array $state, bool $strict = true)
    {
        foreach(self::getProperties(static::$only['dump']) as $name => $property)
        {
            $this->__set($name, $state[$name] ?? $property->getDefaultValue());
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
            $__freezer = (array)json_decode($this->state['__freezer'], true);

            if (isset($__freezer['hash'])) {
                $hash = self::$storage->getFreezer()->generateHash($this);

                if ($__freezer['hash'] === $hash) {
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
     * Creates a new property for the object.
     *
     * @return \Cryo\Property
     */
    private static function createProperty(string $name, array $params): Property
    {
        $params = array_replace(Property::DEFAULT_PARAMS, $params);
        $class  = sprintf('\\Cryo\\Property\\%sProperty', ucfirst($params['type']));

        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('%s property is not defined.', ucfirst($params['type']))
            );
        }

        return new $class($name, $params);
    }

    /**
     * Converts the property parameter array to property objects.
     *
     */
    private static function initializeProperties()
    {
        static::$properties['__freezer'] = array('only' => 'dump');
        static::$properties['__key']     = array('type' => 'key');

        foreach(static::$properties as $name => &$property) {
            $property = self::createProperty($name, $property);

            $only = $property->getOnly();
            if (!empty($only) && !in_array($name, static::$only[$only])) {
                array_push(static::$only[$only], $name);
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
