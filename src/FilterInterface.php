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
 * Interface for event filters.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
interface FilterInterface
{
    /**
     * Evaluates an event for forwarding.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean|null TRUE if the event should be allowed, FALSE if it should be rejected,
     *         or NULL if it should be ignored (ie. the event does not fall under the scope of
     *         this filter)
     */
    public function filter(EventInterface $event);
}
