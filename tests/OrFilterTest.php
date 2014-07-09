<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-eventfilter for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\EventFilter
 */

namespace Phergie\Irc\Tests\Plugin\React\EventFilter;

use Phake;
use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Plugin\React\EventFilter\OrFilter;

/**
 * Tests for the OrFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class OrFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testFilter().
     *
     * @return array
     */
    public function dataProviderFilter()
    {
        $data = array();

        $returns = array(
            array(false, false, false),
            array(true, false, true),
            array(false, true, true),
            array(true, true, true),
        );

        foreach ($returns as $return) {
            $filter1 = $this->getMockFilter($return[0]);
            $filter2 = $this->getMockFilter($return[1]);
            $data[] = array(
                array($filter1, $filter2),
                $return[2],
            );
        }

        return $data;
    }

    /**
     * Tests filter().
     *
     * @param array $filters
     * @param boolean $expected
     * @dataProvider dataProviderFilter
     */
    public function testFilter(array $filters, $expected)
    {
        $event = Phake::mock('\Phergie\Irc\Event\EventInterface');
        $filter = new OrFilter($filters);
        $this->assertSame($expected, $filter->filter($event));
    }

    /**
     * Returns a mock filter.
     *
     * @param boolean $return Return value for filter()
     * @return \Phergie\Irc\Event\EventInterface
     */
    protected function getMockFilter($return)
    {
        $filter = Phake::mock('\Phergie\Irc\Plugin\React\EventFilter\FilterInterface');
        Phake::when($filter)
            ->filter($this->isInstanceOf('\Phergie\Irc\Event\EventInterface'))
            ->thenReturn($return);
        return $filter;
    }
}
