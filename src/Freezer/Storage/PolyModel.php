<?php
namespace Cryo\Freezer\Storage;

use Cryo\Key;

class PolyModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected function doStore(array $frozen_object)
    {
        // initialize blacklist
        $this->blacklist = array();

        $object  = &$frozen_object['objects'][$frozen_object['root']];
        $class   = &$object['class'];
        $classes = $class::getClassHierarchy();

        foreach($classes as $i => $class) {
            $child_class = $classes[$i + 1] ?? null;

            if ($child_class === null ||
                $child_class::getTable() !== $class::getTable()) {

                $key = parent::doStore($frozen_object);

                // add stored properties to blacklist
                $this->blacklist = array_merge(
                    $this->blacklist,
                    array_keys($class::getProperties($class::getPrimaryKey()))
                );

                if ($i === 0) {
                    // update object keys
                    $object['state'] = array_merge(
                        $object['state'],
                        $key->getIdPair()
                    );

                    // remove any aggregate objects from list
                    $frozen_object['objects'] = array((string)$key => $object);
                }
            }
        }

        return $key;
    }

    protected function query(Key $key, string $class, array $id): \PdoStatement
    {
        $id = $key->getIdPair();
        $classes = $class::getClassHierarchy();

        $base_class = array_shift($classes);
        $base_table = $base_class::getTable();
        $table  = $base_table;
        $tables = array($table);

        $columns = array();
        $filters = array();

        foreach($id as $name => $value) {
            $column = sprintf('%s.%s', $base_table, $name);
            array_push($columns, $column);
            $filters[$column] = $value;
        }

        $properties = array_keys($class::getProperties(array_keys($id)));
        $columns = array_merge($columns, $properties);

        foreach($classes as $class) {
            if ($table !== $class::getTable()) {
                $table = $class::getTable();
                $on = array();

                foreach($id as $name => $value) {
                    $column = sprintf('%s.%s', $base_table, $name);
                    $on[$column] = sprintf('%s.%s', $table, $name);
                }
            }
            $tables[$table] = $on;
        }

        return $this->db->select($columns, $tables, $filters);
    }
}
