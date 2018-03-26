<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Author;
use Cryo\Test\_files\PolyEntry;
use Cryo\Test\_files\PolyEntryDated;
use Cryo\Test\DatabaseTestCase;

class PolyModelTest extends DatabaseTestCase
{
    private $entry;

    public function setUp()
    {
        parent::setUp();

        $this->entry_dated = new PolyEntryDated(
            array(
                'author'  => new Author(array('id'   => 4, 'name' => 'quinn')),
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\PolyModel::doStore
     * @covers Cryo\Freezer\Storage\PolyModel::buildQueryStatement
     * @covers Cryo\Freezer\Storage\PolyModel::getClassHierarchy
     * @covers Cryo\Freezer\Storage\Model::doFetch
     */
    public function testPutInsertsPolyModelObject()
    {
        $key = $this->entry_dated->put();

        $saved = PolyEntryDated::getByKey($key);

        $this->assertEquals($this->entry_dated, $saved);
    }

}
