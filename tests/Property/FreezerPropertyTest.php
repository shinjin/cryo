<?php
namespace Cryo\Test\Property;

use Cryo\Property\FreezerProperty;
use PHPUnit\Framework\TestCase;

class FreezerPropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $this->property = new FreezerProperty;
    }

    /**
     * @covers Cryo\Property\FreezerProperty::validate
     */
    public function testValidatePassesArrayOrStringValue()
    {
        $this->assertSame(array(), $this->property->validate(array()));
        $this->assertSame('     ', $this->property->validate('     '));
    }

    /**
     * @covers Cryo\Property\FreezerProperty::validate
     * @expectedException \Cryo\Exception\InvalidArgumentException
     */
    public function testValidateThrowsExceptionIfValueIsNeitherArrayNorString()
    {
        $this->property->validate(1);
    }

}
