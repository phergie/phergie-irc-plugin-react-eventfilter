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

/**
 * Forwards events that either are not user-specific or originate from
 * specified users.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class UserFilter implements FilterInterface
{
    /**
     * List of masks identifying users from whom to forward events
     *
     * @var array
     */
    protected $masks;

    /**
     * Accepts a list of masks identifying users from whom to forward events.
     *
     * @param array $masks
     * @see http://www.ircbeginner.com/opvinfo/masks.html
     */
    public function __construct(array $masks)
    {
        $this->masks = $masks;
    }

    /**
     * Filters events that are not user-specific or are from the specified users.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean TRUE if the event is not user-specific or originated
     *         from a user with a matching mask associated with this filter,
     *         FALSE otherwise
     */
    public function filter(EventInterface $event)
    {
        if (!$event instanceof UserEventInterface) {
            return true;
        }

        $userMask = sprintf('%s!%s@%s',
            $event->getNick(),
            $event->getUsername(),
            $event->getHost()
        );

        foreach ($this->masks as $mask) {
            $pattern = '/^' . str_replace('*', '.*', $mask) . '$/';
            if (preg_match($pattern, $userMask)) {
                return true;
            }
        }

        return false;
    }
}
