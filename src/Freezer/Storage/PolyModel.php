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
                    // update class and store object
                    $class = $parentClass;
                    $key = parent::doStore($frozenObject);

                    // if key has changed, update object id
                    $encodedKey = (string)$key;
                    if ($frozenObject['root'] !== $encodedKey) {
                        $frozenObject['root'] = $encodedKey;

                        foreach($key->getIdPair() as $name => $value) {
                            $object['state'][$name] = $value;
                        }
                    }

                    // add stored properties to blacklist
                    $this->blacklist = array_merge(
                        $this->blacklist,
                        array_keys($class::getProperties($class::getPrimaryKey()))
                    );

                    // remove any aggregate objects from list
                    $frozenObject['objects'] = array($encodedKey => $object);
                }
            }
        }

        return $key;
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($encodedKey, array &$objects = array())
    {
        return parent::doFetch($encodedKey, $objects);
    }
}
