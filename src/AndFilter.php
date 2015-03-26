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
 * Forwards events that pass all of a given set of filters.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class AndFilter extends CompositeFilter
{
    /**
     * Filters events that pass all contained filters.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean TRUE if the event passes all contained filters, FALSE
     *         otherwise
     */
    public function filter(EventInterface $event)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($event)) {
                return false;
            }
        }
        return true;
    }
}
