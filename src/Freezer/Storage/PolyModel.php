<?php
namespace Cryo\Freezer\Storage;

class PolyModel extends Model
{
    /**
     * @inheritdoc
     */
    protected function doStore(array $frozenObject)
    {
        $object = &$frozenObject['objects'][$frozenObject['root']];
        $class  = &$object['class'];

        // get ancestors
        $parents = array_values(class_parents($class, false));
        array_unshift($parents, $class);

        foreach(array_reverse($parents) as $parentClass) {
            if (strpos($parentClass, 'Cryo\\Model') !== 0) {
                $i = array_search($parentClass, $parents);
                $childClass = $i !== 0 ? $parents[$i - 1] : false;

                if ($childClass === false ||
                    $childClass::getTable() !== $parentClass::getTable()) {
                    $class = $parentClass;
                    parent::doStore($frozenObject);
                    $frozenObject['objects'] = array($object);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($encodedKey, array &$objects = array())
    {
        return parent::doFetch($encodedKey, $objects);
    }
}
