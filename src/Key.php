<?php
namespace Cryo;

use Cryo\Model;
use Cryo\Exception\InvalidArgumentException;

class Key
{
    /**
     * Object's class name.
     *
     * @var string
     */
    private $class;

    /**
     * Object's id.
     *
     * @var array
     */
    private $id;

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

        if (!empty($encoded)) {
            list($this->class, $this->id) = $this->decode($encoded);
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
     *
     * @return \Cryo\Key
     */
    public static function generate(string $class, $id): Key
    {
        $key = new static;
        $key->class = $class;
        $key->setId($id);
        return $key;
    }

    /**
     * Sets the object's class name.
     *
     * @param string $class The object's class name
     *
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function setClass(string $class): void
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
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function setId($id): void
    {
        $id = (array)$id;

        if (empty($id) || count($id) !== count(array_filter($id))) {
            throw new InvalidArgumentException('Id must be non-empty value.');
        }

        $this->id = array_values($id);
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
        return base64_encode(json_encode(array($this->class, $this->id)));
    }

    /**
     * Converts the key's encoded string to array of properties.
     *
     * @return array Array containing key's class and id.
     */
    private function decode(string $encoded): array
    {
        return json_decode(base64_decode($encoded), true);
    }
}
