<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Author;
use Cryo\Test\_files\Expando;
use Cryo\Test\_files\ExpandoFixed;
use Cryo\Test\DatabaseTestCase;

class ExpandoTest extends DatabaseTestCase
{
    private $expando;
    private $fixed;

    public function setUp()
    {
        parent::setUp();

        $this->expando = new Expando;
        $this->fixed   = new ExpandoFixed(
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
        $this->fixed->id = 1;
        $this->assertSame(1, $this->fixed->id);
    }

    /**
     * @covers Cryo\Model\Expando::__set
     * @covers Cryo\Model::__get
     */
    public function testSetsDynamicPropertyValue()
    {
        $this->fixed->dynamic = 1;
        $this->assertSame(1, $this->fixed->dynamic);
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
    public function testGetsExpandoFixedObject()
    {
        $expando = ExpandoFixed::get(2);

        $this->assertInstanceOf('Cryo\\Test\\_files\\ExpandoFixed', $expando);
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
    public function testPutInsertsExpandoFixedObject()
    {
        $this->fixed->put();
        $saved = ExpandoFixed::get(4);

        $this->assertEquals($this->fixed, $saved);
    }

}
