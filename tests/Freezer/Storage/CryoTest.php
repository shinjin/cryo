<?php
namespace Cryo\Test\Freezer\Storage;

use Cryo\Freezer\Storage\Cryo;
use Cryo\Test\DatabaseTestCase;
use Shinjin\Pdo\Db;

class CryoTest extends DatabaseTestCase
{
    public function setUp()
    {
        
    }

    /**
     * @covers Cryo\Freezer\Storage\Cryo::__construct
     */
    public function testConstructorWithDefaultArguments()
    {
        $db = new Db(array('driver' => 'sqlite'));
        $storage = new Cryo($db);

        $this->assertInstanceOf('\\Cryo\\Freezer\\Storage\\Cryo', $storage);
        $this->assertInstanceOf('\\Freezer\\Freezer', $storage->getFreezer());
        $this->assertFalse($storage->getUseLazyLoad());
    }
}
