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
use Phergie\Irc\Event\UserEventInterface;
use Phergie\Irc\Plugin\React\UserMode\Plugin as UserModePlugin;

/**
 * Forwards events that either are not channel-specific or originate from
 * specified channels.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class ChannelFilter implements FilterInterface
{
    /**
     * List of channels from which to allow events
     *
     * @var array
     */
    protected $channels;

    /**
     * Mapping of commands to corresponding names of parameters containing
     * channel names
     *
     * @var array
     */
    protected $parameters = array(
        'JOIN' => 'channels',
        'PART' => 'channels',
        'MODE' => 'target',
        'TOPIC' => 'channel',
        'KICK' => 'channel',
        'PRIVMSG' => 'receivers',
    );

    /**
     * Error code for when $channels contains an invalid channel name value
     */
    const ERR_CHANNELS_INVALID = 1;

    /**
     * Accepts a list of channels from which to allow events.
     *
     * @param array $channels
     * @throws \RuntimeException $channels contains an invalid channel name
     *         value
     */
    public function __construct(array $channels)
    {
        $filtered = array_filter(
            $channels,
            function($channel) {
                return !(is_string($channel)
                    && preg_match('/^[#&]/', $channel));
            }
        );
        if ($filtered) {
            $formatted = implode(', ', array_map(
                function($value) {
                    return var_export($value, true);
                },
                $filtered
            ));
            throw new \RuntimeException(
                '$channels contains invalid values: ' . $formatted,
                self::ERR_CHANNELS_INVALID
            );
        }

        $this->channels = $channels;
    }

    /**
     * Filters events that are not channel-specific or originate in specified
     * channels.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean TRUE if the event is not channel-specific or originated
     *         from a matching channel associated with this filter, FALSE
     *         otherwise
     */
    public function filter(EventInterface $event)
    {
        if (!$event instanceof UserEventInterface) {
            return true;
        }

        $channels = $this->getChannels($event);
        $commonChannels = array_intersect($channels, $this->channels);
        if ($commonChannels) {
            return true;
        }

        return false;
    }

    /**
     * Extracts a list of channel names from a user event.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @return array
     */
    protected function getChannels(UserEventInterface $event)
    {
        $command = $event->getCommand();
        if (isset($this->parameters[$command])) {
            $params = $event->getParams();
            $param = $this->parameters[$command];
            return preg_grep('/^[#&]/', explode(',', $params[$param]));
        }
        return array();
    }
}
