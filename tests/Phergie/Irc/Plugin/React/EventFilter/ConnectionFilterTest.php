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

/**
 * Tests for the ConnectionFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class ConnectionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests filter().
     */
    public function testFilter()
    {
        $connection = $this->getMockConnection();
        Phake::when($connection)->getNick()->thenReturn('nick');

        $otherConnection = $this->getMockConnection();
        Phake::when($otherConnection)->getNick()->thenReturn('otherNick');

        $filteredConnection = $this->getMockConnection();
        Phake::when($filteredConnection)->getNick()->thenReturn('filteredNick');

        $filter = new ConnectionFilter(array($connection, $otherConnection));
        $event = Phake::mock('\Phergie\Irc\Event\EventInterface');

        Phake::when($event)->getConnection()->thenReturn($connection);
        $this->assertTrue($filter->filter($event));

        Phake::when($event)->getConnection()->thenReturn($otherConnection);
        $this->assertTrue($filter->filter($event));

        Phake::when($event)->getConnection()->thenReturn($filteredConnection);
        $this->assertFalse($filter->filter($event));
    }

    /**
     * Tests passing an invalid connection to the constructor.
     */
    public function testConstructWithInvalidConnection()
    {
        try {
            $filter = new ConnectionFilter(array('foo'));
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame(ConnectionFilter::ERR_CONNECTIONS_INVALID, $e->getCode());
        }
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
