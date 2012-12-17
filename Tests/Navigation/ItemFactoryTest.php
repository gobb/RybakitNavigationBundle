<?php

namespace Rybakit\Bundle\NavigationBundle\Tests\Navigation;

use Rybakit\Bundle\NavigationBundle\Navigation\Item;
use Rybakit\Bundle\NavigationBundle\Navigation\ItemFactory;

class ItemFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $traversalIndex = 0;

        $filter = $this->getMock('\\Rybakit\\Bundle\\NavigationBundle\\Navigation\\Filter\\FilterInterface');
        $filter->expects($this->any())
            ->method('apply')
            ->will($this->returnCallback(function($options, Item $item) use (&$traversalIndex) {
                $item->label = $options['label'];
                $item->__index = $traversalIndex++;
            }));

        $factory = new ItemFactory($filter, new Item());

        $root = $factory->create(array(
            'label'     => '0',
            'children'  => array(
                array('label' => '1.1'),
                array('label' => '1.2', 'children'  => array(array('label' => '1.2.1'))),
                array('label' => '1.3'),
            ),
        ));

        // post-order traversal
        $this->assertEquals('(4:0)(0:1.1)(2:1.2)(1:1.2.1)(3:1.3)', $this->dumpItem($root));
    }

    /**
     * TODO refactor testSetFilter()
     */
    public function testSetFilter()
    {
        $item = $this->getMock('\\Rybakit\\Bundle\\NavigationBundle\\Navigation\\ItemInterface');

        $defaultFilter = $this->getMock('\\Rybakit\\Bundle\\NavigationBundle\\Navigation\\Filter\\FilterInterface');
        $defaultFilter->expects($this->once())->method('apply')->with($this->equalTo(array('label' => 'root')));

        $customFilter = $this->getMock('\\Rybakit\\Bundle\\NavigationBundle\\Navigation\\Filter\\FilterInterface');
        $customFilter->expects($this->once())->method('apply')->with($this->equalTo(array('label' => '*root*')));

        $factory = new ItemFactory($defaultFilter, $item);
        $factory->create(array('label' => 'root'));
        $factory->setFilter($customFilter);
        $factory->create(array('label' => '*root*'));
    }

    protected function dumpItem(Item $item)
    {
        $dump = sprintf('(%d:%s)', $item->__index, $item->label);

        foreach ($item as $child) {
            $dump .= $this->dumpItem($child);
        }

        return $dump;
    }
}
