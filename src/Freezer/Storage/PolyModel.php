<?php
namespace Cryo\Freezer\Storage;

use Cryo\Key;

class PolyModel extends Model
{
    /**
     * @inheritdoc
     */
    protected function doStore(array $frozen_object)
    {
        $object  = &$frozen_object['objects'][$frozen_object['root']];
        $class   = &$object['class'];
        $classes = $this->getClassHierarchy($class);

        foreach($classes as $class) {
            $i = array_search($class, $classes);
            $child_class = $i + 1 === count($classes) ? null : $classes[$i + 1];

            if ($child_class === null ||
                $child_class::getTable() !== $class::getTable()) {

                $key = parent::doStore($frozen_object);

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
                $frozen_object['objects'] = array((string)$key => $object);
            }
        }

        return $key;
    }

    protected function buildQueryStatement(Key $key, string $class): string
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

    private function getClassHierarchy(string $class): array
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
