<?php
namespace Cryo\Property;

use Cryo\Property;
use Cryo\Exception\InvalidArgumentException;

class FreezerProperty extends ArrayProperty
{
    protected static $type = 'array';

    public function validate($value)
    {
        if (gettype($value) !== 'string' && $value !== null) {
            $value = parent::validate($value);
        }

        return $value;
    }
}
