<?php
namespace Cryo\Test\Property;

use Cryo\Property\ArrayProperty;
use PHPUnit\Framework\TestCase;

class ArrayPropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $params = array('reference' => '\\Cryo\\Test\\Model\\AuthorModel');
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
            '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzFdXQ==',
            '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzJdXQ==',
            '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzNdXQ=='
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
            '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzFdXQ==',
            '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzJdXQ==',
            array(
                '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzNdXQ=='
            )
        );

        $this->assertSame($expected, $this->property->makeValueFromDb($values));
    }
}
