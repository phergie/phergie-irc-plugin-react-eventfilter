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

use Phake;
use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Plugin\React\UserMode\Plugin as UserModePlugin;

/**
 * Tests for the UserModeFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class UserModeFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testFilter().
     *
     * @return array
     */
    public function dataProviderFilter()
    {
        $data = array();

        // Not an instance of UserEventInterface
        $userMode = $this->getMockUserModePlugin(array());
        $data[] = array($userMode, Phake::mock('\Phergie\Irc\Event\EventInterface'), true);

        // User does not have needed modes
        $event = $this->getMockUserEvent();
        $data[] = array($userMode, $event, false);

        // User has needed modes
        $userMode = $this->getMockUserModePlugin(array('o'));
        $data[] = array($userMode, $event, true);

        return $data;
    }

    /**
     * Tests filter().
     *
     * @param \Phergie\Irc\Plugin\React\UserMode\Plugin $userMode
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param boolean $expected
     * @dataProvider dataProviderFilter
     */
    public function testFilter(UserModePlugin $userMode, EventInterface $event, $expected)
    {
        $filter = new UserModeFilter($userMode, array('o', 'h'));
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Returns a mock UserMode plugin.
     *
     * @param array $result Return value of calls to the plugin's filter()
     *        method
     * @return \Phergie\Irc\Plugin\React\UserMode\Plugin
     */
    protected function getMockUserModePlugin($result)
    {
        $userMode = Phake::mock('\Phergie\Irc\Plugin\React\UserMode\Plugin');
        Phake::when($userMode)
            ->getUserModes(
                    $this->isInstanceOf('\Phergie\Irc\ConnectionInterface'),
                    $this->isType('string'),
                    $this->isType('string')
                )
            ->thenReturn($result);
        return $userMode;
    }

    /**
     * Returns a mock user event.
     *
     * @return \Phergie\Irc\Event\UserEventInterface
     */
    protected function getMockUserEvent()
    {
        $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
        Phake::when($event)->getConnection()->thenReturn($this->getMockConnection());
        Phake::when($event)->getNick()->thenReturn('nick');
        Phake::when($event)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($event)->getParams()->thenReturn(array('receivers' => '#channel1'));
        return $event;
    }

    /**
     * Returns a mock connection.
     *
     * @return \Phergie\Irc\ConnectionInterface
     */
    protected function getMockConnection()
    {
        return Phake::mock('\Phergie\Irc\ConnectionInterface');
    }
}
