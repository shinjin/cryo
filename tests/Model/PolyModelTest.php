<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Author;
use Cryo\Test\_files\PolyEntry;
use Cryo\Test\_files\PolyEntryDated;
use Cryo\Test\_files\PolySpecial;
use Cryo\Test\DatabaseTestCase;

class PolyModelTest extends DatabaseTestCase
{
    private $entry;

    public function setUp()
    {
        parent::setUp();

        $this->special = new PolySpecial(
            array(
                'special' => 'special value'
            )
        );

        $this->dated_entry = new PolyEntryDated(
            array(
                'author'  => new Author(array('id' => 4, 'name' => 'quinn')),
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );
    }

    /**
     * @covers Cryo\Model::get
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\PolyModel::createStorage
     * @covers Cryo\Freezer\Storage\Model::doFetch
     * @covers Cryo\Freezer\Storage\PolyModel::query
     */
    public function testGetsPolyModelObject()
    {
        $object = PolyEntryDated::get(1);

        $this->assertInstanceOf('Cryo\\Test\\_files\\PolyEntryDated', $object);
        $this->assertSame(array(1), $object->__key->getId());
    }

    /**
     * @covers Cryo\Model::get
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\PolyModel::createStorage
     * @covers Cryo\Freezer\Storage\Model::doFetch
     * @covers Cryo\Freezer\Storage\PolyModel::query
     */
    public function testGetsPolyModelConcreteObject()
    {
        $object = PolySpecial::get(1);

        $this->assertInstanceOf('Cryo\\Test\\_files\\PolySpecial', $object);
        $this->assertSame(array(1), $object->__key->getId());
        $this->assertSame('special value', $object->special);
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model::__get
     * @covers Cryo\Freezer\Storage\PolyModel::doStore
     * @covers Cryo\Freezer\Storage\PolyModel::query
     * @covers Cryo\Freezer\Storage\Model::doStore
     * @covers Cryo\Freezer\Storage\Model::doFetch
     */
    public function testPutInsertsPolyModelObject()
    {
        $key = $this->dated_entry->put();
        $saved = PolyEntryDated::getByKey($key);

        $this->assertEquals($this->dated_entry, $saved);
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\PolyModel::doStore
     * @covers Cryo\Freezer\Storage\PolyModel::query
     * @covers Cryo\Freezer\Storage\Model::doStore
     * @covers Cryo\Freezer\Storage\Model::doFetch
     */
    public function testPutInsertsPolyModelConcreteObject()
    {
        $key = $this->special->put();
        $saved = PolySpecial::getByKey($key);

        $this->assertEquals($this->special, $saved);
    }

    /**
     * @covers Cryo\Model\PolyModel::delete
     * @covers Cryo\Model::getDb
     * @covers Cryo\Model::getTable
     * @expectedException \Freezer\Exception\ObjectNotFoundException
     */
    public function testDeletesPolyModelObject()
    {
        $entry   = PolyEntryDated::get(1);
        $deleted = $entry->delete();

        $this->assertSame(2, $deleted);        
        PolyEntryDated::get(1);
    }

    /**
     * @covers Cryo\Model\PolyModel::delete
     * @expectedException \Cryo\Exception\NotSavedException
     */
    public function testDeleteThrowsNotSavedException()
    {
        $this->dated_entry->delete();
    }
}
