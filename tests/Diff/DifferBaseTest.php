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
use CHItA\PHPDiff\Differ3;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;
use PHPUnit\Framework\TestCase;

class DifferBaseTest extends TestCase
{
    /**
     * @var Differ3
     */
    private $differ;

    public function setUp(): void
    {
        $this->differ = new Differ3(
            new WeaveMerge(new Differ(null, new Hirschberg()))
        );
    }

    public function testSetLCS()
    {
        $this->differ->setLCSAlgorithm(null);
        $this->assertEquals($this->differ->getLCSAlgorithm(), null);

        $lcs = new Hirschberg();
        $this->differ->setLCSAlgorithm($lcs);
        $this->assertEquals($this->differ->getLCSAlgorithm(), $lcs);
    }

    public function testSetDataSequencingStrategy()
    {
        $sqncing = $this->getMockBuilder('\CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface')
            ->getMock();

        $this->differ->setDataSequencingStrategy($sqncing);
        $this->assertEquals($this->differ->getDataSequencingStrategy(), $sqncing);
    }

    public function testSetComparisonAlgorithm()
    {
        $stub = $this->getMockBuilder('\CHItA\PHPDiff\Comparison\ComparisonInterface')
            ->getMock();

        $stub->method('compare')
            ->willReturn(true);

        $this->differ->setComparisonAlgorithm($stub);
        $this->assertEquals($stub, $this->differ->getComparisonAlgorithm());
    }

    public function testSetLCSStrategy()
    {
        $lcs = new Hirschberg();

        $stub = $this->getMockBuilder('\CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface')
            ->getMock();

        $stub->method('getLCS')
            ->willReturn($lcs);

        $this->differ->setLCSStrategy($stub);
        $this->assertEquals($this->differ->getLCSAlgorithm(), null);
        $this->assertEquals($this->differ->getLCSStrategy(), $stub);
    }

    public function testGetSequence()
    {
        $reflection = new \ReflectionClass('\CHItA\PHPDiff\DifferBase');
        $reflectionMethod = $reflection->getMethod('getSequence');
        $reflectionMethod->setAccessible(true);

        $sqncing = $this->getMockBuilder('\CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface')
            ->getMock();

        $sqncing->method('getSequence')
            ->will($this->returnCallback('str_split'));

        $this->differ->setDataSequencingStrategy($sqncing);
        $this->assertEquals(array('a', 'b', 'c'), $reflectionMethod->invoke($this->differ, 'abc'));
    }
}
