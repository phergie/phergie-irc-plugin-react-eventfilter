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
 * Forwards events that occur on a specific connection.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class ConnectionFilter implements FilterInterface
{
    /**
     * Connection over which to allow events to be forwarded
     *
     * @var \Phergie\Irc\ConnectionInterface
     */
    protected $connection;

    /**
     * Accepts the connection over which to allow events to be forwarded.
     *
     * @param \Phergie\Irc\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
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
        return $event->getConnection() === $this->connection;
    }
}
