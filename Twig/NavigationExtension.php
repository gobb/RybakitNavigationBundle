<?php

namespace Rybakit\Bundle\NavigationBundle\Twig;

use Rybakit\Bundle\NavigationBundle\Navigation\Item;
use Rybakit\Bundle\NavigationBundle\Navigation\ItemInterface;
use Rybakit\Bundle\NavigationBundle\Navigation\Iterator\BreadcrumbIterator;
use Rybakit\Bundle\NavigationBundle\Navigation\Iterator\CustomFilterIterator;
use Rybakit\Bundle\NavigationBundle\Navigation\Iterator\TreeIterator;

class NavigationExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var \Twig_Template
     */
    protected $template;

    /**
     * @param \Twig_Template|string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'nav' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'tree'        => new \Twig_Filter_Method($this, 'addTreeFilter'),
            'visible'     => new \Twig_Filter_Method($this, 'addVisibilityFilter'),
            'breadcrumbs' => new \Twig_Filter_Method($this, 'addBreadcrumbFilter'),
            'ancestor'    => new \Twig_Filter_Method($this, 'getAncestor'),
        );
    }

    /**
     * @param \Traversable $iterator
     * @param int|null     $depth
     *
     * @return TreeIterator
     */
    public function addTreeFilter(\Traversable $iterator, $depth = null)
    {
        $iterator = new TreeIterator($iterator);

        if (null !== $depth) {
            $iterator->setMaxLevel((int) $depth - 1);
        }

        return $iterator;
    }

    /**
     * @param \RecursiveIterator $iterator
     * @param bool               $isVisible
     *
     * @return CustomFilterIterator
     */
    public function addVisibilityFilter(\RecursiveIterator $iterator, $isVisible = true)
    {
        return new CustomFilterIterator($iterator, function($item) use ($isVisible) {
            return $item instanceof Item && $isVisible == $item->isVisible();
        });
    }

    /**
     * @param ItemInterface $item
     *
     * @return BreadcrumbIterator
     */
    public function addBreadcrumbFilter(ItemInterface $item)
    {
        return new BreadcrumbIterator($item);
    }

    /**
     * @param ItemInterface $item
     * @param int           $level
     *
     * @return ItemInterface|null
     */
    public function getAncestor(ItemInterface $item, $level)
    {
        if ($level >= 0) {
            $breadcrumbs = iterator_to_array(new BreadcrumbIterator($item), false);
            $breadcrumbs = array_reverse($breadcrumbs);

            return isset($breadcrumbs[$level]) ? $breadcrumbs[$level] : null;
        }

        do {
            $item = $item->getParent();
            ++$level;
        } while ($item && $level < 0);

        return $item;
    }

    /**
     * Renders a navigation.
     *
     * @see https://github.com/fabpot/Twig/pull/926
     *
     * @param mixed  $item
     * @param string $block
     * @param array  $options
     *
     * @return string
     */
    public function render($item, $block, array $options = array())
    {
        return $this->getTemplate()->renderBlock($block, array(
            'items'   => $item,
            'options' => $options,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rybakit_navigation';
    }

    /**
     * @return \Twig_Template
     */
    protected function getTemplate()
    {
        if (!$this->template instanceof \Twig_Template) {
            $this->template = $this->environment->loadTemplate($this->template);
        }

        return $this->template;
    }
}
