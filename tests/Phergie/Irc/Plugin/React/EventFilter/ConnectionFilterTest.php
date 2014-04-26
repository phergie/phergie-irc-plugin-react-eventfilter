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
        $connection = Phake::mock('\Phergie\Irc\ConnectionInterface');
        $filter = new ConnectionFilter($connection);
        $event = Phake::mock('\Phergie\Irc\Event\EventInterface');

        Phake::when($event)->getConnection()->thenReturn($connection);
        $this->assertTrue($filter->filter($event));

        $otherConnection = Phake::mock('\Phergie\Irc\ConnectionInterface');
        Phake::when($event)->getConnection()->thenReturn($otherConnection);
        $this->assertFalse($filter->filter($event));
    }
}
