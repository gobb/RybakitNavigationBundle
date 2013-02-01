<?php

namespace Rybakit\Bundle\NavigationBundle\Navigation;

use Rybakit\Bundle\NavigationBundle\Navigation\Filter\FilterInterface;

class Item implements ItemInterface
{
    protected $parent;
    protected $children = array();
    protected $attributes;

    public function __construct(array $options, FilterInterface $filter = null)
    {
        if (isset($options['children'])) {
            foreach ($options['children'] as $childOptions) {
                $this->addChild(new static($childOptions, $filter));
            }
            unset($options['children']);
        }

        if ($filter) {
            $filter->apply($options, $this);
        }

        $this->attributes = $options;
    }

    public function setParent(self $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function addChild(self $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }
}
