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

use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Event\EventInterface;

/**
 * Forwards events that occur on a specific connection.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class ConnectionFilter implements FilterInterface
{
    /**
     * Connections over which to allow events to be forwarded
     *
     * @var \Phergie\Irc\ConnectionInterface[]
     */
    protected $connections;

    /**
     * Error code for when $connections contains an invalid connection
     */
    const ERR_CONNECTIONS_INVALID = 1;

    /**
     * Accepts the connections over which to allow events to be forwarded.
     *
     * @param \Phergie\Irc\ConnectionInterface[] $connections
     */
    public function __construct($connections)
    {
        $filtered = array_filter(
            $connections,
            function($connection) {
                return !$connection instanceof ConnectionInterface;
            }
        );
        if ($filtered) {
            throw new \RuntimeException(
                'All elements of $connections must implement'
                    . ' \Phergie\Irc\ConnectionInterface',
                self::ERR_CONNECTIONS_INVALID
            );
        }

        $this->connections = $connections;
    }

    /**
     * Filters events over the specified connection.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean TRUE if the event connection matches the one associated
     *         with this filter, FALSE otherwise
     */
    public function filter(EventInterface $event)
    {
        return in_array($event->getConnection(), $this->connections);
    }
}
