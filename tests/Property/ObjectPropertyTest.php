<?php
namespace Cryo\Test\Property;

use Cryo\Property\ObjectProperty;
use PHPUnit\Framework\TestCase;

class ObjectPropertyTest extends TestCase
{
    private $property;

    protected function setUp()
    {
        $params = array('reference' => '\\Cryo\\Test\\_files\\Author');
        $this->property = new ObjectProperty('test', $params);
    }

    /**
     * @covers Cryo\Property\ObjectProperty::__construct
     */
    public function testConstructorWorks()
    {
        $this->assertInstanceOf('\\Cryo\\Property\\ObjectProperty', $this->property);
    }

    /**
     * @covers Cryo\Property\ObjectProperty::makeValueForDb
     */
    public function testMakeValueForDbReplacesFreezerValueWithDbValue()
    {
        $keys  = array('a' => 1);
        $value = '__freezer_a';

        $this->assertSame(1, $this->property->makeValueForDb($value, $keys));
    }

    /**
     * @covers Cryo\Property\ObjectProperty::makeValueFromDb
     */
    public function testMakeValueFromDbReplacesDbValueWithFreezerValue()
    {
        $expected = '__freezer_WyJcXENyeW9cXFRlc3RcXF9maWxlc1xcQXV0aG9yIixbMV1d';

        $this->assertSame($expected, $this->property->makeValueFromDb(1));
    }
}
