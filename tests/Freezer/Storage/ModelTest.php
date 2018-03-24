<?php
namespace Cryo\Test\Freezer\Storage;

use Cryo\Freezer\Storage\Model;
use Cryo\Test\DatabaseTestCase;
use Shinjin\Pdo\Db;

class ModelTest extends DatabaseTestCase
{
    public function setUp()
    {
        
    }

    /**
     * @covers Cryo\Freezer\Storage\Model::__construct
     */
    public function testConstructorWithDefaultArguments()
    {
        $db = new Db(array('driver' => 'sqlite'));
        $storage = new Model($db);

        $this->assertInstanceOf('\\Cryo\\Freezer\\Storage\\Model', $storage);
        $this->assertInstanceOf('\\Freezer\\Freezer', $storage->getFreezer());
        $this->assertFalse($storage->getUseLazyLoad());
    }
}
