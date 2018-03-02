<?php
namespace Cryo\Test;

use Cryo\Property\FloatProperty;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    protected $stub;
    protected $concrete;

    protected function setUp()
    {
        $this->stub = $this->getMockForAbstractClass('\\Cryo\\Property');
        $this->concrete = new FloatProperty('test', array('required' => true));
    }

    protected function tearDown()
    {
        $this->stub = null;
    }

    /**
     * @covers Cryo\Property::__construct
     * @covers Cryo\Property::getDefaultValue
     * @covers Cryo\Property::getOnly
     */
    public function testConstructorWithDefaultArguments()
    {
        $this->assertInstanceOf('\\Cryo\\Property', $this->stub);
        $this->assertSame(null, $this->stub->getDefaultValue());
        $this->assertSame(null, $this->stub->getOnly());
    }

    /**
     * @covers Cryo\Property::__construct
     * @covers Cryo\Property::getType
     */
    public function testConstructorWithTypeAlias()
    {
        $this->assertSame('double', $this->concrete->getType());
    }

    /**
     * @covers Cryo\Property::validate
     */
    public function testValidatesAndReturnsValue()
    {
        $this->assertSame(1.2, $this->concrete->validate(1.2));
    }

    /**
     * @covers Cryo\Property::validate
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionIfValueIsRequiredButNull()
    {
        $this->concrete->validate(null);
    }

    /**
     * @covers Cryo\Property::validate
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionIfValueIsWrongType()
    {
        $this->concrete->validate(1);
    }

    /**
     * @covers Cryo\Property::isEmpty
     */
    public function testEmptyValueReturnsTrue()
    {
        $this->assertTrue($this->stub->isEmpty(null));
    }

    /**
     * @covers Cryo\Property::makeValueForDb
     */
    public function testMakeValueForDbPassesValueAsIs()
    {
        $this->assertSame('test', $this->stub->makeValueForDb('test'));
    }

    /**
     * @covers Cryo\Property::makeValueFromDb
     */
    public function testMakeValueFromDbPassesValueAsIs()
    {
        $this->assertSame('test', $this->stub->makeValueFromDb('test'));
    }
}
