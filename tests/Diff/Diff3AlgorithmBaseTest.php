<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\Test;

use CHItA\PHPDiff\Diff3Algorithm\WeaveMerge;
use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;
use PHPUnit\Framework\TestCase;

class Diff3AlgorithmBaseTest extends TestCase
{
    /**
     * @var WeaveMerge
     */
    private $differ;

    public function setUp(): void
    {
        $this->differ = new WeaveMerge(new Differ(null, new Hirschberg()));
    }

    public function testGetCommonSubstring()
    {
        $reflection = new \ReflectionClass('\CHItA\PHPDiff\Diff3Algorithm\Base');
        $reflectionMethod = $reflection->getMethod('getLeadingCommonSubstring');
        $reflectionMethod->setAccessible(true);

        $sequenceOne = array('a', 'b');
        $sequenceTwo = array('a', 'b', 'c');
        $result = $reflectionMethod->invokeArgs($this->differ, array(&$sequenceOne, &$sequenceTwo));

        $this->assertEquals(array('a', 'b'), $result);
        $this->assertEquals(array(), $sequenceOne);
        $this->assertEquals(array('c'), $sequenceTwo);

        $stub = $this->getMockBuilder('\CHItA\PHPDiff\Comparison\ComparisonInterface')
            ->getMock();

        $stub->method('compare')
            ->willReturn(true);

        $this->differ->setComparisonAlgorithm($stub);

        $sequenceOne = array('g', 'f');
        $sequenceTwo = array('a', 'b', 'c');
        $result = $reflectionMethod->invokeArgs($this->differ, array(&$sequenceOne, &$sequenceTwo));

        $this->assertEquals(array('g', 'f'), $result);
        $this->assertEquals(array(), $sequenceOne);
        $this->assertEquals(array('c'), $sequenceTwo);
    }
}
