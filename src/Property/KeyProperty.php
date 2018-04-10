<?php
namespace Cryo\Property;

use Cryo\Key;
use Cryo\Property;
use Cryo\Exception\BadKeyException;

class KeyProperty extends Property
{
    public function validate($value)
    {
        if (!$value instanceof Key && !is_string($value)) {
            throw new BadKeyException(
                'Key property must be either a Key object or string.'
            );
        }

        if (is_string($value)) {
            return new Key($value);
        }

        return $value;
    }
}
