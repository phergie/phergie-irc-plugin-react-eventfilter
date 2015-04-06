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
 * Forwards events that do not pass a contained filter.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class NotFilter implements FilterInterface
{
    /**
     * Filter to evaluate
     *
     * @var \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
     */
    protected $filter;

    /**
     * Accepts the filter to use in evaluating events to be forwarded.
     *
     * @param \Phergie\Irc\Plugin\React\EventFilter\FilterInterface $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Filters events that do not pass the contained filter.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean|null TRUE if the contained filter fails, FALSE if it passes,
     *         or NULL if it returns NULL.
     */
    public function filter(EventInterface $event)
    {
        $result = $this->filter->filter($event);
        if ($result === null) {
            return null;
        }
        return !$result;
    }
}
