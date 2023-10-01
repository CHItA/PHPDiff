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

use PHPUnit\Framework\TestCase;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;

abstract class LCSTestBase extends TestCase
{
    /**
     * @var LCSInterface
     */
    protected $LCSAlgorithm;

    /**
     * @param string|array  $sqnc1
     * @param string|array  $sqnc2
     * @param string|array  $expected
     * @dataProvider    LCSTestSet
     */
    public function testLCS($sqnc1, $sqnc2, $expected)
    {
        $sqnc1      = (is_array($sqnc1)) ? $sqnc1 : str_split($sqnc1);
        $sqnc2      = (is_array($sqnc2)) ? $sqnc2 : str_split($sqnc2);
        $expected   = (is_array($expected)) ? $expected : str_split($expected);

        $this->assertEquals($expected, $this->LCSAlgorithm->getLongestCommonSubsequence($sqnc1, $sqnc2));
    }

    /**
     * @param string|array  $sqnc1
     * @param string|array  $sqnc2
     * @param string|array  $expected
     * @dataProvider    LCSTrimTestSet
     */
    public function testLCSWithCustomComparison($sqnc1, $sqnc2, $expected)
    {
        $sqnc1      = (is_array($sqnc1)) ? $sqnc1 : str_split($sqnc1);
        $sqnc2      = (is_array($sqnc2)) ? $sqnc2 : str_split($sqnc2);
        $expected   = (is_array($expected)) ? $expected : str_split($expected);

        $stub = $this->getMockBuilder('\CHItA\PHPDiff\Comparison\ComparisonInterface')
            ->getMock();

        $stub->method('compare')
            ->will($this->returnCallback(function($a, $b) {
                return trim($a) === trim($b);
            }));

        $this->LCSAlgorithm->setComparisonAlgorithm($stub);

        $this->assertEquals($expected, $this->LCSAlgorithm->getLongestCommonSubsequence($sqnc1, $sqnc2));
    }

    public function LCSTestSet()
    {
        return array(
            array(
                'XMJYAUZ',
                'MZJAWXU',
                'MJAU'
            ),
            array(
                'XMJYAUZ',
                'MZJAWX',
                'MJA'
            ),
            array(
                'XMJYAU',
                'MZJAWXU',
                'MJAU'
            ),
            array(
                'ACGTACGT',
                'BBBBB',
                array()
            ),
        );
    }

    public function LCSTrimTestSet()
    {
        return array(
            array(
                'XMJYAUZ',
                'MZJAWXU',
                'MJAU'
            ),
            array(
                'XMJYAUZ',
                array('  M', 'Z  ', 'J', ' A ', 'W', 'X', 'U'),
                'MJAU'
            ),
            array(
                'ACGTACGT',
                'BBBBB',
                array()
            ),
        );
    }
}
