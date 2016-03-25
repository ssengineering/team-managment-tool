<?php

namespace TMT\accessor;

/**
 * Unit tests for the Links accessor class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

class LinkTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers ::getTree
     */
    public function testGetTree() {
		$linksAcc = new \TMT\accessor\Links();
		$links = $linksAcc->getTree(1);

		$link1 = new \TMT\model\Link((object) array(
			"name"       => "link1",
			"resource"   => null,
			"verb"       => null,
			"newTab"     => true,
			"url"        => null,
			"children"   => array()
		));
		$link2 = new \TMT\model\Link((object) array(
			"name"       => "link2",
			"resource"   => null,
			"verb"       => null,
			"newTab"     => false,
			"url"        => null,
			"children"   => array(
				new \TMT\model\Link((object) array(
					"name"       => "child1",
					"resource"   => null,
					"verb"       => null,
					"newTab"     => false,
					"url"        => "/test",
					"children"   => array()
				)),
				new \TMT\model\Link((object) array(
					"name"       => "child2",
					"resource"   => null,
					"verb"       => null,
					"newTab"     => false,
					"url"        => null,
					"children"   => array(
						new \TMT\model\Link((object) array(
							"name"       => "grandchild1",
							"resource"   => null,
							"verb"       => null,
							"newTab"     => false,
							"url"        => "url/path",
							"children"   => array()
						)),
						new \TMT\model\Link((object) array(
							"name"       => "grandchild2",
							"resource"   => null,
							"verb"       => null,
							"newTab"     => false,
							"url"        => null,
							"children"   => array()
						))
					)
				)),
				new \TMT\model\Link((object) array(
					"name"       => "child3",
					"resource"   => null,
					"verb"       => null,
					"newTab"     => false,
					"url"        => null,
					"children"   => array()
				))
			)
		));

		
		$this->assertEquals($link1, $links[0]);
		$this->assertEquals($link2, $links[1]);
    }

}
