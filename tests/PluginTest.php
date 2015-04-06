<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-eventfilter for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Plugin\React\EventFilter
 */

namespace Phergie\Irc\Tests\Plugin\React\EventFilter;

use Phake;
use Phergie\Irc\Bot\React\PluginInterface;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Plugin\React\EventFilter\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Plugin\React\EventFilter\Plugin
     */
    protected $plugin;

    /**
     * Mock plugins
     *
     * @var \Phergie\Irc\Bot\React\PluginInterface[]
     */
    protected $mockPlugins;

    /**
     * Mock filter
     *
     * @var \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
     */
    protected $mockFilter;

    /**
     * Mock logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $mockLogger;

    /**
     * Instantiates and applies a commonly needed configuration to the class
     * under test.
     */
    protected function setUp()
    {
        foreach (range(1, 4) as $index) {
            $this->mockPlugins[$index] = $this->getMockPlugin();
        }
        $this->mockPlugins[5] = new CallbackPlugin;
        $this->mockFilter = $this->getMockFilter();
        $this->plugin = new Plugin(array(
            'plugins' => $this->mockPlugins,
            'filter' => $this->mockFilter,
        ));
        $this->mockLogger = $this->getMockLogger();
        $this->plugin->setLogger($this->mockLogger);
    }

    /**
     * Data provider for testInvalidConfiguration().
     *
     * @return array
     */
    public function dataProviderInvalidConfiguration()
    {
        $data = array();
        $plugin = $this->getMockPlugin();
        $filter = $this->getMockFilter();

        $data[] = array(
            array(
                'plugins' => 'foo',
                'filter' => $filter,
            ),
            Plugin::ERR_PLUGINS_NONARRAY,
        );

        $data[] = array(
            array(
                'plugins' => array(new \stdClass),
                'filter' => $filter,
            ),
            Plugin::ERR_PLUGINS_NONPLUGINS,
        );

        $data[] = array(
            array(
                'plugins' => array($plugin, new \stdClass),
                'filter' => $filter,
            ),
            Plugin::ERR_PLUGINS_NONPLUGINS,
        );

        $data[] = array(
            array(
                'plugins' => array($plugin),
                'filter' => 'foo',
            ),
            Plugin::ERR_FILTER_INVALID,
        );

        return $data;
    }

    /**
     * Tests instantiating the class under test with invalid configuration.
     *
     * @param array $config Configuration to apply
     * @param int $code Expected exception code
     * @dataProvider dataProviderInvalidConfiguration
     */
    public function testInvalidConfiguration(array $config, $code)
    {
        try {
            $plugin = new Plugin($config);
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame($code, $e->getCode());
        }
    }

    /**
     * Tests that custom events without event parameters are forwarded.
     */
    public function testHandleEventForwardsCustomEvents()
    {
        $called = false;
        $args = array('bar', 'baz');
        $test = $this;
        $callback = function() use (&$called, $args, $test) {
            $called = true;
            $test->assertSame($args, func_get_args());
        };
        $event = 'foo';
        Phake::when($this->mockPlugins[1])
            ->getSubscribedEvents()
            ->thenReturn(array($event => $callback));

        $this->plugin->handleEvent($event, $args);

        $this->assertTrue($called);
    }

    /**
     * Tests that handle forwards event with event parameters based on the
     * filter's response.
     */
    public function testHandleEventForwardsEventsPerFilter()
    {
        $test = $this;

        $passEvent = $this->getMockEvent();
        Phake::when($this->mockFilter)
            ->filter($this->identicalTo($passEvent))
            ->thenReturn(true);

        $neutralEvent = $this->getMockEvent();
        Phake::when($this->mockFilter)
            ->filter($this->identicalTo($neutralEvent))
            ->thenReturn(null);

        $failEvent = $this->getMockEvent();
        Phake::when($this->mockFilter)
            ->filter($this->identicalTo($failEvent))
            ->thenReturn(false);

        $passArgs = array($passEvent, 'foo');
        $called = $callbacks = array();
        foreach (range(1, 2) as $index) {
            $called[$index] = false;
            $callbacks[$index] = function() use (&$called, $index, $passArgs, $test) {
                $called[$index] = true;
                $test->assertSame($passArgs, func_get_args());
            };
            Phake::when($this->mockPlugins[$index])
                ->getSubscribedEvents()
                ->thenReturn(array('pass' => $callbacks[$index]));
        }

        $called[3] = false;
        $callbacks[3] = function() use (&$called) {
            $called[3] = true;
        };
        Phake::when($this->mockPlugins[3])
            ->getSubscribedEvents()
            ->thenReturn(array('neutral' => $callbacks[3]));

        $neutralArgs = array($neutralEvent, 'bar');
        $this->plugin->handleEvent('neutral', $neutralArgs);

        $called[4] = false;
        $callbacks[4] = function() use (&$called) {
            $called[4] = true;
        };
        Phake::when($this->mockPlugins[4])
            ->getSubscribedEvents()
            ->thenReturn(array('fail' => $callbacks[4]));

        $this->plugin->handleEvent('pass', $passArgs);

        $failArgs = array($failEvent, 'baz');
        $this->plugin->handleEvent('fail', $failArgs);

        $this->assertTrue($called[1]);
        $this->assertTrue($called[2]);
        $this->assertTrue($called[3]);
        $this->assertFalse($called[4]);
    }

    /**
     * Tests that getSubscribedEvents() returns an array of valid callbacks.
     */
    public function testGetSubscribedEvents()
    {
        $test = $this;

        $fooCalled = false;
        $fooArgs = array('fooArg');
        $fooCallback = function() use (&$fooCalled, $fooArgs, $test) {
            $fooCalled = true;
            $test->assertSame($fooArgs, func_get_args());
        };
        foreach (range(1, 2) as $index) {
            Phake::when($this->mockPlugins[$index])
                ->getSubscribedEvents()
                ->thenReturn(array('foo' => $fooCallback));
        }

        $events = $this->plugin->getSubscribedEvents();
        $this->assertInternalType('array', $events);

        $this->assertArrayHasKey('foo', $events);
        $this->assertInternalType('callable', $events['foo']);
        $this->assertFalse($fooCalled);
        call_user_func_array($events['foo'], $fooArgs);
        $this->assertTrue($fooCalled);

        $barArgs = array('barArg');
        $this->assertArrayHasKey('bar', $events);
        $this->assertInternalType('callable', $events['bar']);
        $this->assertFalse($this->mockPlugins[5]->called);
        call_user_func_array($events['bar'], $barArgs);
        $this->assertTrue($this->mockPlugins[5]->called);
        $this->assertSame($barArgs, $this->mockPlugins[5]->args);
    }

    /**
     * Data provider for testHandleEventWithInvalidCallback().
     *
     * @return array
     */
    public function dataProviderHandleEventWithInvalidCallback()
    {
        $data = array();

        $data[] = array(
            'foo',
            'Plugin returns non-array value for event callbacks',
        );

        $data[] = array(
            array('foo' => 'foo'),
            'Plugin returns invalid event callback',
        );

        return $data;
    }

    /**
     * Tests that handleEvent() logs when a plugin returns invalid
     * callbacks.
     *
     * @param mixed $callbacks Return value for getSubscribedEvents()
     * @param string $message Expected message to be logged
     * @dataProvider dataProviderHandleEventWithInvalidCallback
     */
    public function testHandleEventWithInvalidCallback($callbacks, $message)
    {
        Phake::when($this->mockPlugins[1])
            ->getSubscribedEvents()
            ->thenReturn($callbacks);

        $plugin = new Plugin(array(
            'plugins' => array($this->mockPlugins[1]),
            'filter' => $this->getMockFilter(),
        ));
        $plugin->setLogger($this->mockLogger);

        $plugin->handleEvent('foo', array('fooArgs'));

        Phake::verify($this->mockLogger)->warning($message, $this->isType('array'));
    }

    /**
     * Returns a mock plugin.
     *
     * @return \Phergie\Irc\Bot\React\PluginInterface
     */
    protected function getMockPlugin()
    {
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array());
        return $plugin;
    }

    /**
     * Returns a mock filter.
     *
     * @return \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
     */
    protected function getMockFilter()
    {
        return Phake::mock('\Phergie\Irc\Plugin\React\EventFilter\FilterInterface');
    }

    /**
     * Returns a mock logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function getMockLogger()
    {
        return Phake::mock('\Psr\Log\LoggerInterface');
    }

    /**
     * Returns a mock event.
     *
     * @return \Phergie\Irc\Event\EventInterface
     */
    protected function getMockEvent()
    {
        return Phake::mock('\Phergie\Irc\Event\EventInterface');
    }
}

/**
 * Plugin used to test plugin method callbacks.
 */
class CallbackPlugin implements PluginInterface
{
    public $called = false;
    public $args;

    public function getSubscribedEvents()
    {
        return array(
            'bar' => 'handleBarEvent',
        );
    }

    public function handleBarEvent()
    {
        $this->called = true;
        $this->args = func_get_args();
    }
}
