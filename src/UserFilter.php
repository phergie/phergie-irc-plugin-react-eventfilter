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
    protected $masks = [];

    /**
     * Exception code used when masks are not set or not set as an array
     */
    const ERR_MASKS_NONARRAY = 1;

    /**
     * Accepts filter configuration.
     *
     * Supported keys:
     *
     * masks - an array of masks identifying users from whom to forward events
     *
     * caseless - true to use the caseless preg modifier when comparing masks,
     * true by default
     *
     * @param array $config
     * @see http://www.ircbeginner.com/opvinfo/masks.html
     */
    public function __construct(array $config)
    {
        $this->masks = $this->getMasks($config);
    }

    /**
     * Filters events that are not user-specific or are from the specified users.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return boolean|null TRUE if the event originated from a user with a matching mask
     *         associated with this filter, FALSE if it originated from a user without a
     *         matching mask, or NULL if it did not originate from a user.
     */
    public function filter(EventInterface $event)
    {
        if (!$event instanceof UserEventInterface) {
            return null;
        }

        $nick = $event->getNick();
        if ($nick === null) {
            return null;
        }

        $userMask = sprintf('%s!%s@%s',
            $nick,
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

    /**
     * Validates and extracts masks from configuration
     *
     * @param array $config
     * @return array
     * @throws \RuntimeException Configuration lacks an array of masks
     */
    protected function getMasks(array $config)
    {
        if (!isset($config['masks']) || !is_array($config['masks'])) {
            throw new \RuntimeException(
                'Configuration must contain the "masks" key and reference an array',
                self::ERR_MASKS_NONARRAY
            );
        }

        return $config['masks'];
    }
}
