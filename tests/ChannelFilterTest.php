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
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Plugin\React\EventFilter\ChannelFilter;

/**
 * Tests for the ChannelFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class ChannelFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests passing an invalid channel name to the constructor.
     */
    public function testConstructWithInvalidChannel()
    {
        try {
            $plugin = new ChannelFilter(array('foo'));
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame(ChannelFilter::ERR_CHANNELS_INVALID, $e->getCode());
        }
    }

    /**
     * Data provider for testFilterPasses().
     *
     * @return array
     */
    public function dataProviderFilterPasses()
    {
        $data = array();

        $parameters = array(
            'JOIN' => 'channels',
            'PART' => 'channels',
            'MODE' => 'target',
            'TOPIC' => 'channel',
            'KICK' => 'channel',
            'PRIVMSG' => 'receivers',
        );

        // Not an instance of UserEventInterface
        $data[] = array(Phake::mock('\Phergie\Irc\Event\EventInterface'), true);

        // Supported events in same channels as filter
        foreach (array('#channel1', '&channel2') as $channel) {
            foreach ($parameters as $command => $parameter) {
                $event = $this->getMockUserEvent();
                $params = array($parameter => $channel);
                Phake::when($event)->getCommand()->thenReturn($command);
                Phake::when($event)->getParams()->thenReturn($params);
                $data[] = array($event, true);
            }
        }

        // Unsupported event
        $event = $this->getMockUserEvent();
        Phake::when($event)->getCommand()->thenReturn('QUIT');
        $data[] = array($event, false);

        // Supported events in a different channel from the filter
        foreach ($parameters as $command => $parameter) {
            $event = $this->getMockUserEvent();
            $params = array($parameter => '#channel3');
            Phake::when($event)->getCommand()->thenReturn($command);
            Phake::when($event)->getParams()->thenReturn($params);
            $data[] = array($event, false);
        }

        return $data;
    }

    /**
     * Tests filter().
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param boolean $expected
     * @dataProvider dataProviderFilterPasses
     */
    public function testFilter(EventInterface $event, $expected)
    {
        $filter = new ChannelFilter(array('#channel1', '&channel2'));
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Returns a mock user event.
     *
     * @return \Phergie\Irc\Event\UserEventInterface
     */
    protected function getMockUserEvent()
    {
        return Phake::mock('\Phergie\Irc\Event\UserEventInterface');
    }
}
