<?php
namespace Cryo\Test;

use Cryo\Key;
use Cryo\Exception\InvalidArgumentException;

class KeyTest extends DatabaseTestCase
{
    private $key;

    public function setUp()
    {
        parent::setUp();

        $this->key = new Key;
        $this->key->setClass('\\Cryo\\Test\\Model\\Entry');
        $this->key->setId(1);
    }

    /**
     * @covers Cryo\Key::__construct
     * @covers Cryo\Key::getClass
     * @covers Cryo\Key::getId
     * @covers Cryo\Key::getNamespace
     */
    public function testConstructorWithDefaultArguments()
    {
        $key = new Key;
        $this->assertInstanceOf('\\Cryo\\Key', $key);
        $this->assertSame(null, $key->getClass());
        $this->assertSame(null, $key->getId());
        $this->assertSame(null, $key->getNamespace());
    }

    /**
     * @covers Cryo\Key::__construct
     * @covers Cryo\Key::decode
     * @covers Cryo\Key::getClass
     * @covers Cryo\Key::getId
     * @covers Cryo\Key::getNamespace
     */
    public function testConstructorWithEncodedKeyArgument()
    {
        $key = new Key((string)$this->key);
        $this->assertInstanceOf('\\Cryo\\Key', $key);
        $this->assertSame('\\Cryo\\Test\\Model\\Entry', $key->getClass());
        $this->assertSame(array(1), $key->getId());
        $this->assertSame(null, $key->getNamespace());
    }

    /**
     * @covers Cryo\Key::__toString
     * @covers Cryo\Key::encode
     */
    public function testCastingToStringReturnsEncodedKey()
    {
        $this->assertSame(
            'W251bGwsIlxcQ3J5b1xcVGVzdFxcTW9kZWxcXEVudHJ5IixbMV1d',
            (string)$this->key
        );
    }

    /**
     * @covers Cryo\Key::generate
     * @covers Cryo\Key::setId
     */
    public function testGeneratesNewKey()
    {
        $key = Key::generate('\\Cryo\\Test\\Model\\Entry', 1);
        $this->assertInstanceOf('\\Cryo\\Key', $key);
    }

    /**
     * @covers Cryo\Key::setClass
     */
    public function testSetsClass()
    {
        $this->key->setClass('\\Cryo\\Test\\Model\\Entry');
        $this->assertSame('\\Cryo\\Test\\Model\\Entry', $this->key->getClass());
    }

    /**
     * @covers Cryo\Key::setClass
     * @expectedException InvalidArgumentException
     */
    public function testSetClassThrowsExceptionIfClassIsInvalid()
    {
        $this->key->setClass('InvalidModel');
    }

    /**
     * @covers Cryo\Key::setId
     */
    public function testSetsArrayId()
    {
        $this->key->setId(array(1));
        $this->assertSame(array(1), $this->key->getId());
    }

    /**
     * @covers Cryo\Key::setId
     */
    public function testSetsAssociativeArrayId()
    {
        $this->key->setId(array('id' => 1));
        $this->assertSame(array(1), $this->key->getId());
    }

    /**
     * @covers Cryo\Key::setId
     */
    public function testSetsScalarId()
    {
        $this->key->setId(1);
        $this->assertSame(array(1), $this->key->getId());
    }

    /**
     * @covers Cryo\Key::getIdPair
     * @covers Cryo\Key::setClass
     * @covers Cryo\Key::setId
     */
    public function testGetsIdPair()
    {
        $this->key->setClass('\\Cryo\\Test\\Model\\Entry');
        $this->key->setId(array(1));
        $this->assertSame(array('id' => 1), $this->key->getIdPair());
    }

    /**
     * @covers Cryo\Key::getIdPair
     * @covers Cryo\Key::setId
     * @expectedException RuntimeException
     */
    public function testGetIdPairThrowsExceptionIfClassIsEmpty()
    {
        $key = new Key;
        $key->getIdPair();
    }

    /**
     * @covers Cryo\Key::get
     * @covers Cryo\Key::encode
     */
    public function testGetsInstance()
    {
        $object = $this->key->get();
        $this->assertInstanceOf('\\Cryo\\Test\\Model\\Entry', $object);
    }

    /**
     * @covers Cryo\Key::delete
     * @covers Cryo\Key::get
     */
    public function testDeletesInstance()
    {
        $this->assertSame(1, $this->key->delete());
    }
}
