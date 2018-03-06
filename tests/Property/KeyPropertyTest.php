<?php
namespace Cryo\Test\Property;

use Cryo\Key;
use Cryo\Property\KeyProperty;
use PHPUnit\Framework\TestCase;

class KeyPropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $this->property = new KeyProperty;
    }

    /**
     * @covers Cryo\Property\KeyProperty::validate
     */
    public function testValidatePassesKeyObjectAsIs()
    {
        $key = new Key;

        $this->assertSame($key, $this->property->validate($key));
    }

    /**
     * @covers Cryo\Property\KeyProperty::validate
     */
    public function testValidatePassesKeyStringConvertedToObject()
    {
        $key = new Key;

        $this->assertEquals($key, $this->property->validate((string)$key));
    }

    /**
     * @covers Cryo\Property\KeyProperty::validate
     * @expectedException \Cryo\Exception\InvalidArgumentException
     */
    public function testValidateThrowsExceptionIfValueIsNeitherKeyObjectNorString()
    {
        $this->property->validate(null);
    }

}
