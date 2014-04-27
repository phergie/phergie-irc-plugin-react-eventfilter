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
 * Tests for the NotFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class NotFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests filter().
     */
    public function testFilter()
    {
        $nestedFilter = Phake::mock('\Phergie\Irc\Plugin\React\EventFilter\FilterInterface');
        $event = Phake::mock('\Phergie\Irc\Event\EventInterface');

        $filter = new NotFilter($nestedFilter);

        Phake::when($nestedFilter)->filter($event)->thenReturn(true);
        $this->assertFalse($filter->filter($event));

        Phake::when($nestedFilter)->filter($event)->thenReturn(false);
        $this->assertTrue($filter->filter($event));
    }
}
