<?php
namespace Cryo\Property;

use Cryo\Property;
use Cryo\Exception\InvalidArgumentException;

class FreezerProperty extends ArrayProperty
{
    protected static $type = 'array';

    public function validate($value)
    {
        if (gettype($value) === 'string') {
            $value = json_decode($value, true);
        }

        return parent::validate($value);
    }
}
