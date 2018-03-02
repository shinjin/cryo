<?php
namespace Cryo;

use Cryo\Exception\InvalidArgumentException;

abstract class Property
{
    /**
     * Default property parameters
     *
     * @var array
     */
    const DEFAULT_PARAMS = array(
        'type'      => 'string',
        'default'   => null,
        'required'  => false,
        'only'      => null
    );

    /**
     * The property name
     *
     * @var string
     */
    protected $name;

    /**
     * A list of property parameters:
     *
     * - type:     the value type
     * - default:  the default value
     * - required: whether the value is required
     * - only:     "load" or "dump" to limit when to include the value
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param string $name
     * @param array  $params
     */
    public function __construct(string $name = null, array $params = array())
    {
        $this->name   = $name;
        $this->params = array_replace(self::DEFAULT_PARAMS, $params);

        if (isset(static::$alias)) {
            $this->params['type'] = static::$alias;
        } else {
            $class_name = (new \ReflectionClass($this))->getShortName();
            $this->params['type'] = lcfirst(strstr($class_name, 'Property', true));
        }
    }

    /**
     * Returns the property's default value.
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->params['default'];
    }

    /**
     * Returns the property's only setting.
     *
     * @return string|null
     */
    public function getOnly(): ?string
    {
        return $this->params['only'];
    }

    /**
     * Returns the property's type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->params['type'];
    }

    /**
     * Validates the passed value and returns it.
     *
     * @param  string $value The value to validate.
     *
     * @return mixed
     * @throws \Cryo\Exception\InvalidArgumentException
     */
    public function validate($value)
    {
        if ($this->params['required'] === true && $value === null) {
            $message = sprintf('Property "%s" must be provided.', $this->name);
            throw new InvalidArgumentException($message);
        }

        if (gettype($value) !== $this->params['type'] && $value !== null) {
            $message = sprintf(
                'Property "%s" is %s type and must be %s type.',
                $this->name, gettype($value), $this->params['type']
            );
            throw new InvalidArgumentException($message);
        }

        return $value;
    }

    /**
     * Checks whether the passed value is empty.
     *
     * @param  mixed The value to check.
     *
     * @return boolean
     */
    public function isEmpty($value): bool
    {
        return empty($value);
    }

    /**
     * Converts the value for db input. The default implementation passes
     * the value as is.
     *
     * @param  mixed The value to convert.
     *
     * @return mixed
     */
    public function makeValueForDb($value)
    {
        return $value;
    }

    /**
     * Converts the value to Cryo format. The default implementation passes
     * the value as is.
     *
     * @param  mixed The value to convert.
     *
     * @return mixed
     */
    public function makeValueFromDb($value)
    {
        return $value;
    }
}