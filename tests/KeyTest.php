<?php
namespace Cryo\Test;

use Cryo\Key;

class KeyTest extends DatabaseTestCase
{
    private $key;

    public function setUp()
    {
        parent::setUp();

        $this->key = new Key;
        $this->key->setClass('\\Cryo\\Test\\_files\\Entry');
        $this->key->setId(1);
    }

    /**
     * @covers Cryo\Key::__construct
     * @covers Cryo\Key::getClass
     * @covers Cryo\Key::getId
     */
    public function testConstructorWithDefaultArguments()
    {
        $key = new Key;
        $this->assertInstanceOf('\\Cryo\\Key', $key);
        $this->assertSame(null, $key->getClass());
        $this->assertSame(null, $key->getId());
    }

    /**
     * @covers Cryo\Key::__construct
     * @covers Cryo\Key::decode
     * @covers Cryo\Key::getClass
     * @covers Cryo\Key::getId
     */
    public function testConstructorWithEncodedKeyArgument()
    {
        $key = new Key((string)$this->key);
        $this->assertInstanceOf('\\Cryo\\Key', $key);
        $this->assertSame('\\Cryo\\Test\\_files\\Entry', $key->getClass());
        $this->assertSame(array(1), $key->getId());
    }

    /**
     * @covers Cryo\Key::__construct
     * @covers Cryo\Key::decode
     * @expectedException \Cryo\Exception\BadKeyException
     */
    public function testConstructorThrowsExceptionIfEncodedKeyIsInvalid()
    {
        new Key('abc');
    }

    /**
     * @covers Cryo\Key::__toString
     * @covers Cryo\Key::encode
     */
    public function testCastingToStringReturnsEncodedKey()
    {
        $this->assertSame(
            'WyJcXENyeW9cXFRlc3RcXF9maWxlc1xcRW50cnkiLFsxXV0=',
            (string)$this->key
        );
    }

    /**
     * @covers Cryo\Key::generate
     * @covers Cryo\Key::setId
     */
    public function testGeneratesNewKey()
    {
        $key = Key::generate('\\Cryo\\Test\\_files\\Entry', 1);
        $this->assertInstanceOf('\\Cryo\\Key', $key);
    }

    /**
     * @covers Cryo\Key::setClass
     */
    public function testSetsClass()
    {
        $this->key->setClass('\\Cryo\\Test\\_files\\Entry');
        $this->assertSame('\\Cryo\\Test\\_files\\Entry', $this->key->getClass());
    }

    /**
     * @covers Cryo\Key::setClass
     * @expectedException \Cryo\Exception\BadArgumentException
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
     * @covers Cryo\Key::setId
     * @expectedException \Cryo\Exception\BadArgumentException
     */
    public function testSetIdThrowsExceptionIfIdIsEmpty()
    {
        $this->key->setId(null);
    }

    /**
     * @covers Cryo\Key::getIdPair
     * @covers Cryo\Key::setClass
     * @covers Cryo\Key::setId
     */
    public function testGetsIdPair()
    {
        $this->key->setClass('\\Cryo\\Test\\_files\\Entry');
        $this->key->setId(array(1));
        $this->assertSame(array('id' => 1), $this->key->getIdPair());
    }

    /**
     * @covers Cryo\Key::getIdPair
     * @covers Cryo\Key::setId
     * @expectedException LogicException
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
        $this->assertInstanceOf('\\Cryo\\Test\\_files\\Entry', $object);
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
