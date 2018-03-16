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
use Phergie\Irc\Plugin\React\EventFilter\UserFilter;

/**
 * Tests for the UserFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class UserFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testFilter().
     *
     * @return array
     */
    public function dataProviderFilter()
    {
        $data = [];

        // Not an instance of UserEventInterface
        $data[] = [Phake::mock('\Phergie\Irc\Event\EventInterface'), null];

        // Not originating from a user
        $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
        Phake::when($event)->getNick()->thenReturn(null);
        $data[] = [$event, null];

        // Non-matching user mask
        $event = $this->getMockUserEvent('nick3', 'user3', 'host3');
        $data[] = [$event, false];

        // Matching user masks
        foreach ([1, 2, 21] as $n) {
            $event = $this->getMockUserEvent('nick' . $n, 'user' . $n, 'host' . $n);
            $data[] = [$event, true];
        }

        // Matching freenode style mask
        $event = $this->getMockUserEvent('freenodenick', 'freenodeuser', 'unaffiliated/freenodenick');
        $data[] = [$event, true];

        return $data;
    }

    /**
     * Data provider for testFilterCaseless().
     *
     * @return array
     */
    public function dataProviderFilterCaseless()
    {
        $data = [];

        $event = $this->getMockUserEvent('nick1', 'user1', 'host1');
        $data[] = [$event, true];

        $event = $this->getMockUserEvent('NICK1', 'USER1', 'HOST1');
        $data[] = [$event, true];

        return $data;
    }

    /**
     * Data provider for testFilterCased().
     *
     * @return array
     */
    public function dataProviderFilterCased()
    {
        $data = [];

        $event = $this->getMockUserEvent('nick1', 'user1', 'host1');
        $data[] = [$event, true];

        $event = $this->getMockUserEvent('NICK1', 'USER1', 'HOST1');
        $data[] = [$event, false];

        return $data;
    }

    /**
     * Tests filter().
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param boolean $expected
     * @dataProvider dataProviderFilter
     */
    public function testFilter(EventInterface $event, $expected)
    {
        $filter = new UserFilter($masks = [
            'nick1!user1@host1',
            'nick2*!user2*@host2*',
            'freenodenick!freenodeuser@unaffiliated/freenodenick'
        ]);
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Tests matching caseless masks.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param boolean $expected
     * @dataProvider dataProviderFilterCaseless
     */
    public function testFilterCaseless(EventInterface $event, $expected)
    {
        $filter = new UserFilter($masks = ['nick1!user1@host1'], $caseless = true);
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Tests matching cased masks.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param boolean $expected
     * @dataProvider dataProviderFilterCased
     */
    public function testFilterCased(EventInterface $event, $expected)
    {
        $filter = new UserFilter($masks = ['nick1!user1@host1'], $caseless = false);
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Returns a mock user event.
     *
     * @param string $nick
     * @param string $username
     * @param string $host
     * @return \Phergie\Irc\Event\UserEventInterface
     */
    protected function getMockUserEvent($nick, $username, $host)
    {
        $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
        Phake::when($event)->getNick()->thenReturn($nick);
        Phake::when($event)->getUsername()->thenReturn($username);
        Phake::when($event)->getHost()->thenReturn($host);
        return $event;
    }
}
