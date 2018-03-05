<?php
namespace Cryo;

use Cryo\Model;
use Cryo\Exception\InvalidArgumentException;

class Key
{
    /**
     * The object's class name.
     *
     * @var string
     */
    private $class;

    /**
     * The object's id.
     *
     * @var array
     */
    private $id;

    /**
     * A namespace for the key.
     *
     * @var array
     */
    private $namespace;

    /**
     * Constructor.
     *
     * @param string $encoded The encoded key string
     *
     */
    public function __construct(string $encoded = null)
    {
        $this->class = null;
        $this->id = null;
        $this->namespace = null;

        if (!empty($encoded)) {
            list($this->namespace, $this->class, $this->id) = $this->decode($encoded);
        }
    }

    /**
     * Returns the encoded key string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->encode();
    }

    /**
     * Creates a new key and returns it.
     *
     * @param string        $class     The object's class name
     * @param string|array  $id        The object's id
     * @param string        $namespace A namespace for the key
     *
     * @return \Cryo\Key
     */
    public static function generate(string $class, $id, string $namespace = ''): Key
    {
        $key = new static;
        $key->class = $class;
        $key->setId($id);
        $key->namespace = $namespace;
        return $key;
    }

    /**
     * Sets the object's class name.
     *
     * @param string $class The object's class name
     *
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function setClass($class): void
    {
        if (!is_subclass_of($class, '\\Cryo\\Model')) {
            $message = sprintf('Class %s must be a valid Cryo model.', $class);
            throw new InvalidArgumentException($message);
        }

        $this->class = $class;
    }

    /**
     * Returns the object's class name.
     *
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Sets the object's id.
     *
     * @param string $id The object's id
     *
     */
    public function setId($id): void
    {
        $this->id = array_values((array)$id);
    }

    /**
     * Returns the object's id.
     *
     * @return array|null
     */
    public function getId(): ?array
    {
        return $this->id;
    }

    /**
     * Returns the object's id key/value pair.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getIdPair(): array
    {
        if (empty($this->class)) {
            throw new \RuntimeException('Class name must be defined.');
        }

        return array_combine($this->class::getPrimaryKey(), $this->id);
    }

    /**
     * Returns the object's namespace.
     *
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Fetches the object.
     *
     * @return \Cryo\Model
     */
    public function get(): Model
    {
        return $this->class::getByKey($this->encode());
    }

    /**
     * Deletes the object.
     *
     * @return integer The affected row count.
     */
    public function delete(): int
    {
        return $this->get()->delete();
    }

    /**
     * Converts the key object to base64 encoded string.
     *
     * @return string The encoded key.
     */
    private function encode(): string
    {
        $key = array($this->namespace, $this->class, $this->id);
        return base64_encode(json_encode($key));
    }

    /**
     * Converts the key's encoded string to array of properties.
     *
     * @return array Array containing key's namespace, class, and id.
     */
    private function decode(string $encoded): array
    {
        return json_decode(base64_decode($encoded), true);
    }
}
