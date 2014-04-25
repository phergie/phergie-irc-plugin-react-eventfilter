<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-eventfilter for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\EventFilter
 */

namespace Phergie\Irc\Plugin\React\EventFilter;

use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Event\EventInterface;

/**
 * Forwards events that pass any of a given set of filters.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class OrFilter implements FilterInterface
{
    /**
     * List of filters to apply
     *
     * @param \Phergie\Irc\Plugin\React\EventFilter\FilterInterface[] $filters
     */
    protected $filters;

    /**
     * Accepts a list of filters to apply.
     *
     * @param \Phergie\Irc\Plugin\React\EventFilter\FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $filtered = array_filter(
            function($filter) {
                return !$filter instanceof FilterInterface;
            },
            $filters
        );
        if ($filtered) {
            throw new \RuntimeException(
                'All elements of $filters must implement \Phergie\Irc\Plugin\React\EventFilter\FilterInterface'
            );
        }

        $this->filters = $filter;
    }

    /**
     * Filters events that pass any contained filters.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean TRUE if the event passes any contained filters, FALSE
     *         otherwise
     */
    public function filter(EventInterface $event)
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($event)) {
                return true;
            }
        }
        return false;
    }
}
