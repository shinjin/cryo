<?php
namespace Cryo\Property;

use Cryo\Key;
use Cryo\Property;

class ArrayProperty extends Property
{
    const DEFAULT_PARAMS = array(
        'reference' => null
    );

    public function __construct(string $name = null, array $params = array()){
        parent::__construct($name, $params);

        $this->params = array_replace(self::DEFAULT_PARAMS, $this->params);
    }

    public function makeValueForDb($values, array $keys = array(), $level = 0)
    {
        foreach($values as &$value) {
            if (is_array($value)) {
                $value = $this->makeValueForDb($value, $keys, $level + 1);
            } elseif(strpos($value, '__freezer_') === 0) {
                if ($this->params['reference'] !== null) {
                    list(,$key) = explode('__freezer_', $value);
                    if (isset($keys[$key])) {
                        $value = $keys[$key];
                    }
                }
            }
        }

        if ($level === 0) {
            return json_encode($values);
        }

        return $values;
    }

    public function makeValueFromDb($values)
    {
        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        foreach($values as &$value) {
            if (is_array($value)) {
                $value = $this->makeValueFromDb($value);
            } elseif($this->params['reference'] !== null) {
                $key = Key::generate($this->params['reference'], (integer)$value);
                $value = '__freezer_' . (string)$key;
            }
        }

        return $values;
    }
}
