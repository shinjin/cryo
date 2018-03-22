<?php
namespace Cryo\Test\Model;

use Cryo\Test\_files\Author;
use Cryo\Test\_files\PolyEntry;
use Cryo\Test\DatabaseTestCase;

class PolyModelTest extends DatabaseTestCase
{
    private $entry;

    public function setUp()
    {
        parent::setUp();

        $this->entry = new PolyEntry(
            array(
                // 'id'      => 4,
                'author'  => new Author(array('id'   => 4, 'name' => 'quinn')),
                'content' => 'Hello world!',
                'created' => '2016-04-13'
            )
        );

    }

    /**
     * @covers Cryo\Model\PolyModel::put
     * @covers Cryo\Model::getStorage
     * @covers Cryo\Freezer\Storage\Cryo::doStore
     * @covers Cryo\Freezer\Storage\Cryo::makeValuesForDb
     */
    public function testPutUpdatesPolyModelObject()
    {
$this->entry->put();
        // $entry = PolyEntry::get(1);
        // $entry->content = '';
        // $entry->put();

        // $saved = PolyEntry::get(1);

        // $this->assertSame($entry->content, $saved->content);
    }

}
