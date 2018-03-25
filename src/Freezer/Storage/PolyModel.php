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
        $object = &$frozenObject['objects'][$frozenObject['root']];
        $class  = &$object['class'];

        // get ancestors
        $ancestors = array_values(class_parents($class, false));
        array_unshift($ancestors, $class);

        foreach(array_reverse($ancestors) as $class) {
            if (strpos($class, 'Cryo\\Model') !== 0) {
                $i = array_search($class, $ancestors);
                $childClass = $i === 0 ? false : $ancestors[$i - 1];

                if ($childClass === false ||
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
        }

        return $key;
    }

    protected function buildQueryStatement(Key $key, $class)
    {
        // poly_base INNER JOIN poly_entry ON poly_base.id = poly_entry.id
        //           INNER JOIN poly_entry_dated ON poly_base.id = poly_entry_dated.id

        $pk = $class::getPrimaryKey();

        $parents = array_values(class_parents($class, false));
        array_unshift($parents, $class);

        foreach(array_reverse($parents) as $parent) {
            if (strpos($parent, 'Cryo\\Model') !== 0 &&
                (empty($table) || $table !== $parent::getTable())) {
                $table = $parent::getTable();

                if (empty($from)) {
                    $base = $table;
                    $from = $table;
                } else {
                    $filter = array();
                    foreach($pk as $key) {
                        array_push(
                            $filter,
                            sprintf('%s.%s = %s.%s', $base, $key, $table, $key)
                        );
                    }
                    $filter = implode(' AND ', $filter);
                    $from .= sprintf(' INNER JOIN %s ON %s', $table, $filter);
                }
            }
        }

        $columns = '';
        $filter  = '';
        foreach($pk as $key) {
            $columns .= sprintf('%s.%s,', $base, $key);
            $filter  .= sprintf('%s.%s = ?,', $base, $key);
        }
        $columns .= implode(',', array_keys($class::getProperties($pk)));

        $query = sprintf(
            'SELECT %s FROM %s WHERE %s', $columns, $from, rtrim($filter, ',')
        );

        return $query;
    }
}
