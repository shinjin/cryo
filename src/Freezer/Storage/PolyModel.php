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

    protected function buildQueryStatement(Key $key, string $class): string
    {
        $pk = $class::getPrimaryKey();
        $classes = $class::getClassHierarchy();

        $base_class = array_shift($classes);
        $base_table = $base_class::getTable();
        $tables = $base_table;

        $columns = '';
        $filters = '';

        foreach($pk as $key) {
            $column = sprintf('%s.%s', $base_table, $key);
            $columns .= $column . ',';
            $filters .= $column . ' = ?,';
        }
        $columns .= implode(',', array_keys($class::getProperties($pk)));

        foreach($classes as $class) {
            if (empty($table) || $table !== $class::getTable()) {
                $table = $class::getTable();
                $on = array();

                foreach($pk as $key) {
                    $args = array($base_table, $key, $table, $key);
                    array_push($on, vsprintf('%s.%s = %s.%s', $args));
                }

                $tables .= sprintf(' JOIN %s ON %s', $table, implode(' AND ', $on));
            }
        }

        return sprintf(
            'SELECT %s FROM %s WHERE %s', $columns, $tables, rtrim($filters, ',')
        );
    }
}
