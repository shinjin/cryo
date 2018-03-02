<?php
namespace Cryo\Property;

use Cryo\Key;
use Cryo\Property;

class ObjectProperty extends Property
{
    const DEFAULT_PARAMS = array(
        'reference' => null
    );

    public function __construct(string $name, array $params){
        parent::__construct($name, $params);

        $this->params = array_replace(self::DEFAULT_PARAMS, $this->params);
    }

    public function makeValueForDb($value, array $keys = array())
    {
        list(,$key) = explode('__freezer_', $value);
        if (isset($keys[$key])) {
            $value = $keys[$key];
        }
        return $value;
    }

    public function makeValueFromDb($value)
    {
        $key = Key::generate($this->params['reference'], (integer)$value);
        return '__freezer_' . (string)$key;
    }
}
