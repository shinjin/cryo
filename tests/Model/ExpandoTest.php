<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Author;
use Cryo\Test\_files\Expando;
use Cryo\Test\_files\ExpandoHybrid;
use Cryo\Test\DatabaseTestCase;

class ExpandoTest extends DatabaseTestCase
{
    private $expando;
    private $hybrid;

    public function setUp()
    {
        parent::setUp();

        $this->expando = new Expando;
        $this->hybrid  = new ExpandoHybrid(
            array(
                'id'      => 4,
                'author'  => new Author(array('id'   => 4, 'name' => 'quinn')),
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );

    }

    /**
     * @covers Cryo\Model\Expando::__set
     * @covers Cryo\Model::__get
     * @covers Cryo\Model::getPrimaryKey
     */
    public function testSetsFixedPropertyValue()
    {
        $this->hybrid->id = 1;
        $this->assertSame(1, $this->hybrid->id);
    }

    /**
     * @covers Cryo\Model\Expando::__set
     * @covers Cryo\Model::__get
     */
    public function testSetsDynamicPropertyValue()
    {
        $this->hybrid->dynamic = 1;
        $this->assertSame(1, $this->hybrid->dynamic);
    }

    /**
     * @covers Cryo\Model::get
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     * @covers Cryo\Freezer\Storage\Pdo::doFetch
     */
    public function testGetsExpandoObject()
    {
        $expando = Expando::get(1);

        $this->assertInstanceOf('Cryo\\Test\\_files\\Expando', $expando);
        $this->assertSame(array(1), $expando->__key->getId());
    }

    /**
     * @covers Cryo\Model::get
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     * @covers Cryo\Freezer\Storage\Pdo::doFetch
     */
    public function testGetsExpandoHybridObject()
    {
        $expando = ExpandoHybrid::get(2);

        $this->assertInstanceOf('Cryo\\Test\\_files\\ExpandoHybrid', $expando);
        $this->assertEquals(2, $expando->id);
    }


    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     */
    public function testPutInsertsExpandoObject()
    {
        $key = $this->expando->put();
        $saved = Expando::getByKey($key);

        $this->assertEquals($this->expando, $saved);
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     * @covers Cryo\Freezer\Storage\Pdo::doFetch
     */
    public function testPutInsertsExpandoHybridObject()
    {
        $this->hybrid->put();
        $saved = ExpandoHybrid::get(4);

        $this->assertEquals($this->hybrid, $saved);
    }

}
