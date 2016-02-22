<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\Diff3Algorithm;

use CHItA\PHPDiff\Comparison\ComparisonInterface;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;
use CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface;
use InvalidArgumentException;

/**
 * Interface for three-way merge algorithms
 */
interface AlgorithmInterface
{
    /**
     * Returns the three-way diff
     *
     * @param array $original   The original sequence
     * @param array $modified1  First modified sequence
     * @param array $modified2  Second modified sequence
     *
     * @return array The three-way diff
     */
    public function diff(array $original, array $modified1, array $modified2);

    /**
     * Set the Longest Common Subsequence Algorithm to use
     *
     * @param LCSInterface|null  $algorithm Longest common subsequence algorithm to use
     *
     * @return AlgorithmInterface The Three-way diff algorithm
     *
     * @throws InvalidArgumentException When $algorithm is neither null or an instance of LCSInterface
     */
    public function setLCSAlgorithm($algorithm);

    /**
     * Sets comparison algorithm
     *
     * @param ComparisonInterface|null $algorithm Comparison algorithm
     *
     * @return AlgorithmInterface The Three-way diff algorithm
     *
     * @throws InvalidArgumentException When $algorithm is neither null or an instance of ComparisonInterface
     */
    public function setComparisonAlgorithm($algorithm);

    /**
     * Sets the LCS implementation selecting strategy
     *
     * @param StrategyInterface|null $strategy Longest common subsequnce algorithm selection strategy
     *
     * @return AlgorithmInterface The Three-way diff algorithm
     *
     * @throws InvalidArgumentException When $algorithm is neither null nor an instance of StrategyInterface
     */
    public function setLCSStrategy($strategy);
}
