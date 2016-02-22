<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\LongestCommonSubsequence\Algorithm;

use CHItA\PHPDiff\Comparison\ComparisonInterface;
use InvalidArgumentException;

/**
 * Interface for longest common subsequence finding algorithms
 */
interface LCSInterface
{
    /**
     * Returns the longest common subsequence of the two sequences
     *
     * It is expected that the algorithm returns the elements of the first sequence. This behaviour is
     * expected because the option to set a custom comparison algorithm, which means that the two elements
     * may not match exactly but still be considered the same.
     *
     * @param array $sqnc1  First sequence
     * @param array $sqnc2  Second sequence
     *
     * @return array Array of longest common subsequence
     */
    public function getLongestCommonSubsequence(array $sqnc1, array $sqnc2);

    /**
     * Sets comparison algorithm
     *
     * @param ComparisonInterface|null $algorithm Comparison algorithm
     *
     * @return LCSInterface Longest common subsequence algorithm
     *
     * @throws InvalidArgumentException When $algorithm is neither null or an instance of ComparisonInterface
     */
    public function setComparisonAlgorithm($algorithm);
}
