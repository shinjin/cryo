<?php
namespace Cryo\Model;

use Cryo\Key;
use Cryo\Model;

class PolyModel extends Model
{
    /**
     * @inherits
     */
    public function put(): Key
    {
        $parent_class = get_parent_class($this);

        if (strpos($parent_class, 'Cryo\\Model') !== 0) {
            $parent = new $parent_class($this->state);
            $key = $parent->put();
        }

        return new Key(self::getStorage()->store($this));
    }
}
