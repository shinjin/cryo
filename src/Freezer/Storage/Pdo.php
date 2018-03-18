<?php
namespace Cryo\Freezer\Storage;

use Freezer\Storage\Pdo as BasePdo;

class Pdo extends BasePdo
{
    /**
     * @inheritdoc
     */
    protected function doFetch($id, array &$objects = array())
    {
        $isRoot = empty($objects);

        if (!isset($objects[$id])) {
            $stmt = sprintf('SELECT * FROM %s WHERE id = ?', $this->table);
            $stmt = $this->db->query($stmt, array($id));

            if (($result = $stmt->fetch()) !== false) {
                $object = json_decode($result['body'], true);
            } else {
                return false;
            }

            $object['state'] = array('state' => $object['state']);
            $object['isDirty'] = false;
            $objects[$id] = $object;

            if (!$this->useLazyLoad) {
                $this->fetchArray($object['state'], $objects);
            }
        }

        if ($isRoot) {
            return array('root' => $id, 'objects' => $objects);
        }
    }
}
