<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-eventfilter for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Plugin\React\EventFilter
 */

namespace Phergie\Irc\Plugin\React\EventFilter;

use Phergie\Irc\Event\EventInterface;

/**
 * Base class for other filters that forward events based on responses from a
 * set of contained filters.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
abstract class CompositeFilter implements FilterInterface
{
    /**
     * List of filters to apply
     *
     * @param \Phergie\Irc\Plugin\React\EventFilter\FilterInterface[] $filters
     */
    protected $filters;

    /**
     * Error code for when $filters contains an invalid filter
     */
    const ERR_FILTERS_INVALID = 1;

    /**
     * Accepts a list of filters to apply.
     *
     * @param \Phergie\Irc\Plugin\React\EventFilter\FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $filtered = array_filter(
            $filters,
            function($filter) {
                return !$filter instanceof FilterInterface;
            }
        );
        if ($filtered) {
            throw new \RuntimeException(
                'All elements of $filters must implement'
                    . ' \Phergie\Irc\Plugin\React\EventFilter\FilterInterface',
                self::ERR_FILTERS_INVALID
            );
        }

        $this->filters = $filters;
    }
}
