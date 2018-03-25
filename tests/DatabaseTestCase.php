<?php
namespace Cryo\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

use Cryo\Model;
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
            'cryo' => array(
                array('id' => 'WyJDcnlvXFxUZXN0XFxfZmlsZXNcXEV4cGFuZG8iLFsxXV0=', 'body' => '{"class":"Cryo\\\\Test\\\\_files\\\\Expando","state":{"__freezer":{"hash":"0732a755623247ccc1b1839893c4909e2248c7db"}}}')
            ),
            'entry' => array(
                array('id' => 1, 'content' => 'Hello buddy!', 'author' => 1, 'created' => '2010-04-24', '__freezer' => '{"hash":"1c10ad155cdc8d00b2038af64c116aa2b13a8bf4"}'),
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

        Model::setDb(new Db(self::$pdo));
    }

    private function createTables()
    {
        self::$pdo->query('CREATE TABLE IF NOT EXISTS cryo (
            id   text primary key,
            body text
        )');

        self::$pdo->query('CREATE TABLE IF NOT EXISTS entry (
            id        integer primary key,
            content   text,
            author    text,
            created   date,
            __freezer text
        )');

        self::$pdo->query('CREATE TABLE IF NOT EXISTS author (
            id        integer primary key,
            name      text,
            __freezer text
        )');

        self::$pdo->query('CREATE TABLE IF NOT EXISTS poly_base (
            id        integer primary key,
            __freezer text
        )');

        self::$pdo->query('CREATE TABLE IF NOT EXISTS poly_entry (
            id        integer primary key,
            content   text,
            author    text,
            created   date
        )');
    }
}
