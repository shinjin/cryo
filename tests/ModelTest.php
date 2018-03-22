<?php
namespace Cryo\Test;

use Cryo\Model;
use Cryo\Test\_files\Author;
use Cryo\Test\_files\Entry;
use Cryo\Test\_files\EntryArray;

class ModelTest extends DatabaseTestCase
{
    private $author;
    private $entry;
    private $entry_author;

    public function setUp()
    {
        parent::setUp();

        $this->author = new Author(
            array(
                'id'   => 4,
                'name' => 'quinn'
            )
        );

        $this->entry = new Entry(
            array(
                'id'      => 4,
                'author'  => $this->author,
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );

        $this->entry_array = new EntryArray(
            array(
                'id'      => 4,
                'author'  => array($this->author),
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );
    }

    /**
     * @covers Cryo\Model::__construct
     * @covers Cryo\Model::load
     * @covers Cryo\Model::getProperties
     * @covers Cryo\Model::initializeProperties
     * @covers Cryo\Model::createProperty
     */
    public function testConstructorWithDefaultArguments()
    {
        $expected = array(
            'id' => 4,
            'author' => array(
                'id' => 4,
                'name' => 'quinn'
            ),
            'content' => 'Hello world!',
            'created' => '2016-04-13'
        );

        $this->assertSame($expected, $this->entry->dump());
        $this->assertInstanceOf('\\Cryo\\Test\\_files\\Entry', $this->entry);
    }

    /**
     * @covers Cryo\Model::__construct
     * @covers Cryo\Model::load
     * @covers Cryo\Model::getProperties
     * @covers Cryo\Model::initializeProperties
     * @covers Cryo\Model::createProperty
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsExceptionIfPropertyIsInvalid()
    {
        new class extends \Cryo\Model
        {
            protected static $id   = array('type' => 'integer');
            protected static $name = array('type' => 'invalid');
        };
    }

    /**
     * @covers Cryo\Model::__get
     */
    public function testGetsPropertyValue()
    {
        $this->assertSame(4, $this->entry->id);
    }

    /**
     * @covers Cryo\Model::__get
     * @expectedException InvalidArgumentException
     */
    public function testGetThrowsExceptionIfPropertyIsInvalid()
    {
        $this->author->invalid;
    }

    /**
     * @covers Cryo\Model::__set
     * @covers Cryo\Model::__get
     * @covers Cryo\Model::getPrimaryKey
     */
    public function testSetsPropertyValue()
    {
        $this->entry->id = 1;
        $this->assertSame(1, $this->entry->id);
    }

    /**
     * @covers Cryo\Model::__set
     * @expectedException InvalidArgumentException
     */
    public function testSetThrowsExceptionIfPropertyIsInvalid()
    {
        $this->entry->invalid = 1;
    }

    /**
     * @covers Cryo\Model::__isset
     * @covers Cryo\Model::__get
     */
    public function testPropertyValueIsSetReturnsTrue()
    {
        $this->assertSame(true, isset($this->entry->id));
    }

    /**
     * @covers Cryo\Model::__isset
     * @covers Cryo\Model::__get
     */
    public function testPropertyValueIsSetReturnsFalse()
    {
        $this->entry->content = null;
        $this->assertSame(false, isset($this->entry->null));
    }

    /**
     * @covers Cryo\Model::__toString
     * @covers Cryo\Model::dump
     * @covers Cryo\Model::toArray
     * @covers Cryo\Model::toJson
     */
    public function testToStringReturnsJsonEncodedPropertyValues()
    {
        $expected = '{"id":4,"author":{"id":4,"name":"quinn"},"content":"Hello world!","created":"2016-04-13"}';
        $this->assertSame($expected, (string)$this->entry);
    }

    /**
     * @covers Cryo\Model::getDb
     * @covers Cryo\Model::createDb
     */
    public function testGetsDbObject()
    {
        $reflector = new \ReflectionClass('\\Cryo\\Model');
        $db = $reflector->getProperty('db');
        $db->setAccessible(true);
        $db->setValue(null);

        $this->assertInstanceOf('\\Shinjin\\Pdo\\Db', Model::getDb());
    }

    /**
     * @covers Cryo\Model::setDb
     * @covers Cryo\Model::getDb
     * @covers Cryo\Model::createDb
     */
    public function testSetsDbObject()
    {
        Model::setDb(new \Shinjin\Pdo\Db(self::$pdo));

        $this->assertInstanceOf('\\Shinjin\\Pdo\\Db', Model::getDb());
    }

    /**
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Model::createStorage
     * @covers Cryo\Model::getDb
     */
    public function testGetsStorageObject()
    {
        $reflector = new \ReflectionClass('\\Cryo\\Model');
        $db = $reflector->getProperty('storage');
        $db->setAccessible(true);
        $db->setValue(null);

        $this->assertInstanceOf('\\Cryo\\Freezer\\Storage\\Cryo', Model::getStorage());
    }

    /**
     * @covers Cryo\Model::getTable
     */
    public function testGetsModelTable()
    {
        $this->assertSame('entry', EntryArray::getTable());
    }

    /**
     * @covers Cryo\Model::getPrimaryKey
     */
    public function testGetsModelPrimaryKey()
    {
        $this->assertSame(array('id'), Entry::getPrimaryKey());
    }

    /**
     * @covers Cryo\Model::getProperties
     * @covers Cryo\Model::getReservedProperties
     * @covers Cryo\Model::initializeProperties
     */
    public function testGetsObjectProperties()
    {
        $properties = $this->entry->getProperties();
        $expected = array('id', 'author', 'content', 'created', '__freezer');

        $this->assertSame($expected, array_keys($properties));
        $this->assertInstanceOf('\\Cryo\\Property', current($properties));
    }

    /**
     * @covers Cryo\Model::getPropertyReader
     */
    public function testPropertyReaderReturnsObjectPropertyValues()
    {
        $expected = array(
            'id' => 4,
            'name' => 'quinn',
            '__freezer' => null
        );

        $reader = Entry::getPropertyReader();

        $this->assertSame($expected, $reader($this->author));
    }

    /**
     * @covers Cryo\Model::getByKey
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\Cryo::doFetch
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesFromDb
     */
    public function testGetsObjectByKey()
    {
        $entry = Entry::getByKey(
            'WyJDcnlvXFxUZXN0XFxfZmlsZXNcXEVudHJ5IixbMV1d'
        );

        $this->assertInstanceOf('Cryo\\Test\\_files\\Entry', $entry);
        $this->assertEquals(1, $entry->id);
    }

    /**
     * @covers Cryo\Model::getByKey
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\Cryo::doFetch
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesFromDb
     */
    public function testGetsMultipleObjectsByKeys()
    {
        $entries = Entry::getByKey(
            array(
                'WyJDcnlvXFxUZXN0XFxfZmlsZXNcXEVudHJ5IixbMV1d',
                'WyJDcnlvXFxUZXN0XFxfZmlsZXNcXEVudHJ5IixbMl1d'
            )
        );

        $this->assertInstanceOf('\\Cryo\\Test\\_files\\Entry', $entries[0]);
        $this->assertEquals(1, $entries[0]->id);
        $this->assertEquals(2, $entries[1]->id);
    }

    /**
     * @covers  Cryo\Model::get
     * @covers  Cryo\Model::getByKey
     * @covers  Cryo\Model::getStorage
     * @covers  Cryo\Freezer\Storage\Cryo::doFetch
     * @covers  Cryo\Freezer\Storage\Cryo::makeValuesFromDb
     * @depends testGetsObjectByKey
     */
    public function testGetsObjectById()
    {
        $entry = Entry::get(1);

        $this->assertInstanceOf('\\Cryo\\Test\\_files\\Entry', $entry);
        $this->assertEquals(1, $entry->id);
    }

    /**
     * @covers  Cryo\Model::get
     * @covers  Cryo\Model::getByKey
     * @covers  Cryo\Model::getStorage
     * @covers  Cryo\Freezer\Storage\Cryo::doFetch
     * @covers  Cryo\Freezer\Storage\Cryo::makeValuesFromDb
     * @depends testGetsMultipleObjectsByKeys
     */
    public function testGetsMultipleObjectsByIds()
    {
        $entries = Entry::get(array(1, 2));

        $this->assertInstanceOf('\\Cryo\\Test\\_files\\Entry', $entries[0]);
        $this->assertEquals(1, $entries[0]->id);
        $this->assertEquals(2, $entries[1]->id);
    }

    /**
     * @covers  Cryo\Model::get
     * @covers  Cryo\Model::getByKey
     * @covers  Cryo\Model::getStorage
     * @covers  Cryo\Freezer\Storage\Cryo::doFetch
     * @expectedException \Freezer\Exception\ObjectNotFoundException
     */
    public function testGetThrowsExceptionIfObjectDoesNotExist()
    {
        Entry::get(5);
    }

    /**
     * @covers Cryo\Model::getKey
     * @covers Cryo\Model::get
     * @covers Cryo\Key::getId
     * @covers Cryo\Freezer\Storage\Cryo::doFetch
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesFromDb
     */
    public function testGetsObjectKey()
    {
        $entry = Entry::get(1);
        $key = $entry->getKey();

        $this->assertInstanceOf('Cryo\\Key', $key);
        $this->assertSame(array(1), $key->getId());
    }

    /**
     * @covers Cryo\Model::getKey
     * @expectedException \Cryo\Exception\NotSavedException
     */
    public function testGetKeyThrowsExceptionIfObjectHasNotBeenSaved()
    {
        $this->entry->getKey();
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\Cryo::doStore
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesForDb
     */
    public function testPutUpdatesObject()
    {
        $entry = Entry::get(1);
        $entry->content = '';
        $entry->put();

        $saved = Entry::get(1);

        $this->assertSame($entry->content, $saved->content);
    }

    /**
     * @covers Cryo\Model::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\Cryo::doStore
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesForDb
     */
    public function testPutInsertsObject()
    {
        $this->entry->put();

        $saved = Entry::get(4);

        $this->assertEquals($this->entry, $saved);
    }

    /**
     * @covers  Cryo\Model::put
     * @covers  Cryo\Model::getStorage
     * @covers  Cryo\Key::setId
     * @covers  Cryo\Freezer\Storage\Cryo::doStore
     * @covers  Cryo\Freezer\Storage\Cryo::makeValuesForDb
     * @depends testPutInsertsObject
     */
    public function testPutInsertsObjectWithAutoIncrementId()
    {
        $this->entry->id = null;
        $this->entry->put();
        $this->entry->id = 4;

        $saved = Entry::get(4);

        $this->assertEquals($this->entry, $saved);
    }

    /**
     * @covers Cryo\Model::delete
     * @covers Cryo\Model::getDb
     * @covers Cryo\Model::getTable
     * @expectedException \Freezer\Exception\ObjectNotFoundException
     */
    public function testDeletesObject()
    {
        $entry   = Entry::get(1);
        $deleted = $entry->delete();

        $this->assertSame(1, $deleted);        
        Entry::get(1);
    }

    /**
     * @covers Cryo\Model::delete
     * @covers Cryo\Model::getDb
     * @expectedException \Cryo\Exception\NotSavedException
     */
    public function testDeleteThrowsNotSavedException()
    {
        $this->entry->delete();
    }

    /**
     * @covers Cryo\Model::dump
     * @covers Cryo\Model::toArray
     */
    public function testGetsAllPropertyValues()
    {
        $expected = array(
            'id' => 4,
            'author' => array(
                array(
                    'id' => 4,
                    'name' => 'quinn'
                )
            ),
            'content' => 'Hello world!',
            'created' => '2016-04-13'
        );

        $this->assertEquals($expected, $this->entry_array->dump());
    }

    /**
     * @covers Cryo\Model::load
     * @covers Cryo\Model::dump
     * @covers Cryo\Model::toArray
     */
    public function testSetsAllPropertyValues()
    {
        $data = array(
            'id'      => 5,
            'author'  => $this->author,
            'content' => 'Happy Birthday!',
            'created' => '2017-04-13'
        );

        $this->entry->load($data);

        $data['author'] = array(
            'id' => 4,
            'name' => 'quinn'
        );

        $this->assertEquals($data, $this->entry->dump());
    }

    /**
     * @covers Cryo\Model::isDirty
     * @covers Cryo\Model::getStorage
     */
    public function testObjectIsDirty()
    {
        $this->assertTrue($this->entry->isDirty());

        $entry = Entry::get(1);
        $this->assertFalse($entry->isDirty());        
    }

    /**
     * @covers Cryo\Model::isSaved
     */
    public function testObjectIsSaved()
    {
        $this->assertFalse($this->entry->isSaved());

        $entry = Entry::get(1);
        $this->assertTrue($entry->isSaved());        
    }

    /**
     * @covers Cryo\Model::toJson
     * @covers Cryo\Model::toArray
     * @covers Cryo\Model::dump
     */
    public function testJsonEncodesPropertyValues()
    {
        $expected = '{"id":4,"author":{"id":4,"name":"quinn"},"content":"Hello world!","created":"2016-04-13"}';

        $this->assertSame($expected, $this->entry->toJson());      
    }
}
