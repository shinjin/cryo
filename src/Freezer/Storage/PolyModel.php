<?php
namespace Cryo\Freezer\Storage;

class PolyModel extends Model
{
    /**
     * @inheritdoc
     */
    protected function doStore(array $frozenObject)
    {
        // get root object
        $object = $frozenObject['objects'][$frozenObject['root']];

        // get class
        $class = $object['class'];

        // initialize
        if (empty($frozenObject['ancestors'])) {
            $frozenObject['ancestors'] = array_values(class_parents($class, false));
            array_unshift($frozenObject['ancestors'], $class);
        }

        // get parent class
        $parentClass = get_parent_class($class);

        // if parent class exists
        if (strpos($parentClass, 'Cryo\\Model') !== 0) {
            // change object class to parent class
            $parent = $frozenObject;
            $parent['objects'][$parent['root']]['class'] = $parentClass;
            $frozenObject = $this->doStore($parent);
        }

        // get child class
        $i = array_search($class, $frozenObject['ancestors']);
        $childClass = $i !== 0 ? $frozenObject['ancestors'][$i - 1] : false;

        // if child class does not exist OR table does not equal child table
        if ($childClass === false ||
            $childClass::getTable() !== $class::getTable()) {

            $frozenObject['keys'] = parent::doStore($frozenObject);
            // add stored properties (except primary key) to blacklist
            $frozenObject['blacklist'] = array_merge(
                $frozenObject['blacklist'] ?? array(),
                array_keys($class::getProperties($class::getPrimaryKey()))
            );
        }

        return $frozenObject;
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($encodedKey, array &$objects = array())
    {
        return parent::doFetch($encodedKey, $objects);
    }
}
