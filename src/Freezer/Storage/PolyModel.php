<?php
namespace Cryo\Freezer\Storage;

use Cryo\Key;

class PolyModel extends Model
{
    /**
     * @inheritdoc
     */
    protected function doStore(array $frozenObject)
    {
        $object  = &$frozenObject['objects'][$frozenObject['root']];
        $class   = &$object['class'];
        $classes = $this->getClassHierarchy($class);

        foreach($classes as $class) {
            $i = array_search($class, $classes);
            $childClass = $i + 1 === count($classes) ? null : $classes[$i + 1];

            if ($childClass === null ||
                $childClass::getTable() !== $class::getTable()) {

                $key = parent::doStore($frozenObject);

                // update object keys
                $object['state'] = array_replace(
                    $object['state'],
                    $key->getIdPair()
                );

                // add stored properties to blacklist
                $this->blacklist = array_merge(
                    $this->blacklist,
                    array_keys($class::getProperties($class::getPrimaryKey()))
                );

                // remove any aggregate objects from list
                $frozenObject['objects'] = array((string)$key => $object);
            }
        }

        return $key;
    }

    protected function buildQueryStatement(Key $key, $class)
    {
        $pk = $class::getPrimaryKey();
        $classes = $this->getClassHierarchy($class);

        $base_class = array_shift($classes);
        $base_table = $base_class::getTable();
        $from = $base_table;

        $columns = '';
        $filters = '';

        foreach($pk as $key) {
            $columns .= sprintf('%s.%s,', $base_table, $key);
            $filters .= sprintf('%s.%s = ?,', $base_table, $key);
        }
        $columns .= implode(',', array_keys($class::getProperties($pk)));

        foreach($classes as $class) {
            if (empty($table) || $table !== $class::getTable()) {
                $table = $class::getTable();
                $on = array();

                foreach($pk as $key) {
                    array_push(
                        $on,
                        sprintf('%s.%s = %s.%s', $base_table, $key, $table, $key)
                    );
                }

                $from .= sprintf(' JOIN %s ON %s', $table, implode(' AND ', $on));
            }
        }

        return sprintf(
            'SELECT %s FROM %s WHERE %s', $columns, $from, rtrim($filters, ',')
        );
    }

    private function getClassHierarchy($class)
    {
        $classes = array_reverse(
            array_values(
                array_diff_key(
                    class_parents($class, false),
                    array_flip(array('Cryo\\Model', 'Cryo\\Model\\PolyModel'))
                )
            )
        );
        array_push($classes, $class);

        return $classes;
    }
}
