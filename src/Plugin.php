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

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Bot\React\PluginInterface;
use Phergie\Irc\Event\EventInterface;

/**
 * Plugin for limiting processing of incoming events based on event metadata.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class Plugin extends AbstractPlugin
{
    /**
     * @var \Phergie\Irc\Bot\React\PluginInterface[]
     */
    protected $plugins;

    /**
     * @var \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
     */
    protected $filter;

    /**
     * Exception code used when 'plugins' is not an array
     */
    const ERR_PLUGINS_NONARRAY = 1;

    /**
     * Exception code used when 'plugins' contains non-plugin objects
     */
    const ERR_PLUGINS_NONPLUGINS = 2;

    /**
     * Exception code used when 'filter' is not set or contains a non-filter
     * object
     */
    const ERR_FILTER_INVALID = 3;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     * plugins - an array of plugin instances for which to filter incoming
     * events
     *
     * filter - a filter instance to apply to incoming events before forwarding
     * them to contained plugins
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->plugins = $this->getPlugins($config);
        $this->filter = $this->getFilter($config);
    }

    /**
     * Validates and extracts plugins from configuration.
     *
     * @param array $config
     * @return \Phergie\Irc\Bot\React\PluginInterface[]
     * @throws \RuntimeException Configuration lacks a valid plugin list
     */
    protected function getPlugins(array $config)
    {
        if (!isset($config['plugins']) || !is_array($config['plugins'])) {
            throw new \RuntimeException(
                'Configuration must contain "plugins" key referencing an array',
                self::ERR_PLUGINS_NONARRAY
            );
        }

        $filtered = array_filter(
            $config['plugins'],
            function($plugin) {
                return !$plugin instanceof PluginInterface;
            }
        );
        if ($filtered) {
            throw new \RuntimeException(
                'Configuration "plugins" key must contain only objects'
                    . ' implementing \Phergie\Irc\Bot\React\PluginInterface',
                self::ERR_PLUGINS_NONPLUGINS
            );
        }

        return $config['plugins'];
    }

    /**
     * Validates and extracts filter from configuration.
     *
     * @return \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
     * @throws \RuntimeException Configuration lacks a valid filter
     */
    protected function getFilter(array $config)
    {
        if (!isset($config['filter']) || !$config['filter'] instanceof FilterInterface) {
            throw new \RuntimeException(
                'Configuration must contain a "filter" key referencing an object'
                    . ' implementing \Phergie\Irc\Plugin\EventFilter\FilterInterface',
                self::ERR_FILTER_INVALID
            );
        }

        return $config['filter'];
    }

    /**
     * Returns callbacks that invoke a central handler for events to which
     * contained plugins subscribe.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = array();
        $self = $this;
        foreach ($this->plugins as $plugin) {
            foreach (array_keys($plugin->getSubscribedEvents()) as $event) {
                if (isset($events[$event])) {
                    continue;
                }
                $events[$event] = function() use ($event, $self) {
                    $self->handleEvent($event, func_get_args());
                };
            }
        }
        return $events;
    }

    /**
     * Applies filters to events and forwards those that pass to contained
     * plugins.
     *
     * @param string $event Name of the intercepted event, used to forward
     *        the event to listeners
     * @param array $args Event arguments
     */
    public function handleEvent($event, array $args)
    {
        $logger = $this->getLogger();

        $eventObjects = array_filter($args, function($arg) {
            return $arg instanceof EventInterface;
        });
        if (!$eventObjects) {
            $logger->info('Event emitted without EventInterface argument', array(
                'event' => $event,
                'args' => $args,
            ));
        } else {
            $eventObject = reset($eventObjects);
            if (!$this->filter->filter($eventObject)) {
                $logger->info('Event did not pass filter, skipping', array(
                    'event' => $event,
                    'args' => $args,
                ));
                return;
            } else {
                $logger->info('Event passed filter, forwarding', array(
                    'event' => $event,
                    'args' => $args,
                ));
            }
        }

        $callbacks = $this->getEventHandlers($event);
        foreach ($callbacks as $callback) {
            $logger->info('Forwarding event to callback', array(
                'event' => $event,
                'args' => $args,
                'callback' => $callback,
            ));
            call_user_func_array($callback, $args);
        }
    }

    /**
     * Returns handlers from contained plugins for a specified event.
     *
     * @param string $event
     * @return array
     */
    protected function getEventHandlers($event)
    {
        $logger = $this->getLogger();
        $handlers = array();

        foreach ($this->plugins as $plugin) {
            $callbacks = $plugin->getSubscribedEvents();
            if (!is_array($callbacks)) {
                $logger->warning('Plugin returns non-array value for event callbacks', array(
                    'plugin' => get_class($plugin),
                    'callbacks' => $callbacks,
                ));
                continue;
            }
            if (!isset($callbacks[$event])) {
                $logger->debug('Plugin does not handle event', array(
                    'plugin' => get_class($plugin),
                    'event' => $event,
                ));
                continue;
            }
            $callback = $callbacks[$event];
            $pluginCallback = array($plugin, $callback);
            if (is_callable($pluginCallback)) {
                $handlers[] = $pluginCallback;
            } elseif (is_callable($callback)) {
                $handlers[] = $callback;
            } else {
                $logger->warning('Plugin returns invalid event callback', array(
                    'plugin' => get_class($plugin),
                    'event' => $event,
                    'callback' => $callback,
                ));
            }
        }

        return $handlers;
    }
}
