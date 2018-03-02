<?php
namespace Cryo\Test\Property;

use Cryo\Property\ReferenceProperty;
use PHPUnit\Framework\TestCase;

class ReferencePropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $params = array('class' => '\\Cryo\\Test\\Model\\AuthorModel');
        $this->property = new ReferenceProperty('test', $params);
    }

    /**
     * @covers Cryo\Property\ReferenceProperty::__construct
     */
    public function testConstructorWorks()
    {
        $this->assertInstanceOf('\\Cryo\\Property\\ReferenceProperty', $this->property);
    }

    /**
     * @covers Cryo\Property\ReferenceProperty::makeValueForDb
     */
    public function testMakeValueForDbReplacesFreezerValueWithDbValue()
    {
        $keys  = array('a' => 1);
        $value = '__freezer_a';

        $this->assertSame(1, $this->property->makeValueForDb($value, $keys));
    }

    /**
     * @covers Cryo\Property\ReferenceProperty::makeValueFromDb
     */
    public function testMakeValueFromDbReplacesDbValueWithFreezerValue()
    {
        $expected = '__freezer_WyIiLCJcXENyeW9cXFRlc3RcXE1vZGVsXFxBdXRob3JNb2RlbCIsWzFdXQ==';

        $this->assertSame($expected, $this->property->makeValueFromDb(1));
    }
}
