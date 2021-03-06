<?php

namespace Rybakit\Bundle\NavigationBundle\Navigation\Filter\Matcher;

class OptionMatcher implements MatcherInterface
{
    /**
     * @var string An option name
     */
    protected $name;

    /**
     * @var mixed An option value
     */
    protected $value;

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __construct($name, $value)
    {
        $this->name  = (string) $name;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function match($value)
    {
        if (is_array($value) && isset($value[$this->name])) {
            return $this->value === $value[$this->name];
        }

        return false;
    }
}
