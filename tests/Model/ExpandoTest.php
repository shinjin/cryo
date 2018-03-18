<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Expando;
use Cryo\Test\_files\ExpandoEntry;
use Cryo\Test\DatabaseTestCase;

class ExpandoTest extends DatabaseTestCase
{
    private $expando;
    private $entry;

    public function setUp()
    {
        parent::setUp();

        $this->expando = new Expando;
        $this->entry   = new ExpandoEntry;
    }

    /**
     * @covers Cryo\Model\Expando::__set
     * @covers Cryo\Model::__get
     * @covers Cryo\Model::getPrimaryKey
     */
    public function testSetsFixedPropertyValue()
    {
        $this->entry->id = 1;
        $this->assertSame(1, $this->entry->id);
    }

    /**
     * @covers Cryo\Model\Expando::__set
     * @covers Cryo\Model::__get
     */
    public function testSetsDynamicPropertyValue()
    {
        $this->entry->dynamic = 1;
        $this->assertSame(1, $this->entry->dynamic);
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     * @covers Cryo\Freezer\Storage\Cryo::doFetch
     * @covers Cryo\Freezer\Storage\Cryo::doStore
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesForDb
     */
    public function testPutInsertsObject()
    {
        $key = $this->expando->put();
        $saved = Expando::getByKey($key);

        $this->assertEquals($this->expando, $saved);
    }

}
