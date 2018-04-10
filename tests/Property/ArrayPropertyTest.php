<?php
namespace Cryo\Test\Property;

use Cryo\Exception\BadValueException;
use Cryo\Property\ArrayProperty;
use PHPUnit\Framework\TestCase;

class ArrayPropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $params = array('reference' => '\\Cryo\\Test\\__files\\Author');
        $this->property = new ArrayProperty('test', $params);
    }

    /**
     * @covers Cryo\Property\ArrayProperty::__construct
     */
    public function testConstructorWorks()
    {
        $this->assertInstanceOf('\\Cryo\\Property\\ArrayProperty', $this->property);
    }

    /**
     * @covers Cryo\Property\ArrayProperty::validate
     */
    public function testValidatePassesArrayOrStringValue()
    {
        $this->assertSame(array(), $this->property->validate(array()));
        $this->assertSame(array(), $this->property->validate(''));
    }

    /**
     * @covers Cryo\Property\ArrayProperty::validate
     * @expectedException \Cryo\Exception\BadValueException
     */
    public function testValidateThrowsExceptionIfValueIsNeitherArrayNorString()
    {
        $this->property->validate(1);
    }

    /**
     * @covers Cryo\Property\ArrayProperty::makeValueForDb
     */
    public function testMakeValueForDbReplacesArrayOfFreezerValuesWithDbValues()
    {
        $keys   = array('a' => 1, 'b' => 2, 'c' => 3);
        $values = array('__freezer_a', '__freezer_b', '__freezer_c');

        $expected = '[1,2,3]';

        $this->assertSame($expected, $this->property->makeValueForDb($values, $keys));
    }

    /**
     * @covers Cryo\Property\ArrayProperty::makeValueForDb
     */
    public function testMakeValueForDbReplacesNestedArrayOfFreezerValuesWithDbValues()
    {
        $keys   = array('a' => 1, 'b' => 2, 'c' => 3);
        $values = array('__freezer_a', '__freezer_b', array('__freezer_c'));

        $expected = '[1,2,[3]]';

        $this->assertSame($expected, $this->property->makeValueForDb($values, $keys));
    }

    /**
     * @covers Cryo\Property\ArrayProperty::makeValueFromDb
     */
    public function testMakeValueFromDbReplacesDbStringWithArrayOfFreezerValues()
    {
        $values = '[1,2,3]';

        $expected = array(
            '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzFdXQ==',
            '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzJdXQ==',
            '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzNdXQ=='
        );

        $this->assertSame($expected, $this->property->makeValueFromDb($values));
    }

    /**
     * @covers Cryo\Property\ArrayProperty::makeValueFromDb
     */
    public function testMakeValueFromDbReplacesDbStringWithNestedArrayOfFreezerValues()
    {
        $values = '[1,2,[3]]';

        $expected = array(
            '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzFdXQ==',
            '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzJdXQ==',
            array(
                '__freezer_WyJcXENyeW9cXFRlc3RcXF9fZmlsZXNcXEF1dGhvciIsWzNdXQ=='
            )
        );

        $this->assertSame($expected, $this->property->makeValueFromDb($values));
    }
}
