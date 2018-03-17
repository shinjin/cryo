<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Expando;
use Cryo\Test\DatabaseTestCase;

class ExpandoTest extends DatabaseTestCase
{
    private $expando;

    public function setUp()
    {
        parent::setUp();

        $this->expando = new Expando;
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model\Expando::createStorage
     * @covers Cryo\Freezer\Storage\Cryo::doFetch
     * @covers Cryo\Freezer\Storage\Cryo::doStore
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesForDb
     */
    public function testExpandoPutInsertsObject()
    {
        $key = $this->expando->put();
        $saved = Expando::getByKey($key);

        $this->assertEquals($this->expando, $saved);
    }

}
