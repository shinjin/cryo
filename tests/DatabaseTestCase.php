<?php
namespace Cryo\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

use Cryo\Model;
use Cryo\Freezer\Storage\Cryo;
use Freezer\Freezer;
use Shinjin\Pdo\Db;

abstract class DatabaseTestCase extends TestCase
{
    use TestCaseTrait {
        setUp as protected traitSetUp;
    }

    static protected $pdo = null;
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO('sqlite::memory:');
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
            $this->createTables();
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return $this->createArrayDataSet(array(
            'entry' => array(
                array('id' => 1, 'content' => 'Hello buddy!', 'author' => 1, 'created' => '2010-04-24', '__freezer' => '{"hash":"9dc61df140467bc68efc0432cdc59a4f5fa39ae1"}'),
                array('id' => 2, 'content' => 'I like it!',   'author' => 2, 'created' => '2010-04-26', '__freezer' => '{}'),
                array('id' => 3, 'content' => 'Hello world!', 'author' => 3, 'created' => '2010-05-01', '__freezer' => '{}')
            ),
            'author' => array(
                array('id' => 1, 'name' => 'joe', '__freezer' => '{}'),
                array('id' => 2, 'name' => 'nancy', '__freezer' => '{}'),
                array('id' => 3, 'name' => 'suzy', '__freezer' => '{}')
            ),
        ));
    }

    public function setUp()
    {
        $this->traitSetUp();

        $reflector = new \ReflectionClass('\\Cryo\\Model');
        $db = $reflector->getProperty('db');
        $db->setAccessible(true);
        $db->setValue(new Db(self::$pdo));
    }

    private function createTables()
    {
        self::$pdo->query('CREATE TABLE IF NOT EXISTS entry (
            id        integer primary key,
            content   varchar(255),
            author    text,
            created   date,
            __freezer varchar(255)
        )');

        self::$pdo->query('CREATE TABLE IF NOT EXISTS author (
            id        integer primary key,
            name      varchar(255),
            __freezer varchar(255)
        )');
    }
}
