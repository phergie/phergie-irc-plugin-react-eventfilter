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
use Phergie\Irc\Plugin\React\EventFilter\AndFilter;
use Phergie\Irc\Plugin\React\EventFilter\ComposerFilter;

/**
 * Tests for the CompositeFilter class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\EventFilter
 */
class CompositeFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests passing an invalid filter to the constructor.
     */
    public function testConstructWithInvalidFilter()
    {
        try {
            $filter = $this->getFilter(array('foo'));
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame(AndFilter::ERR_FILTERS_INVALID, $e->getCode());
        }
    }

    /**
     * Tests passing a valid filter to the constructor.
     */
    public function testConstructWithValidFilter()
    {
        $filter = $this->getFilter(array(
            Phake::mock('\Phergie\Irc\Plugin\React\EventFilter\FilterInterface')
        ));
    }

    /**
     * Returns a partial mock of the class under test.
     *
     * @param array $args Constructor arguments
     * @return \Phergie\Irc\Plugin\React\EventFilter\CompositeFilter
     */
    protected function getFilter(array $args)
    {
        return Phake::partialMock('\Phergie\Irc\Plugin\React\EventFilter\CompositeFilter', $args);
    }
}
