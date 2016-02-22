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

/**
 * Class implementing a Dynamic Programming approach to obtain the LCS
 */
final class DynamicProgramming extends Base
{
    /**
     * Constructor
     *
     * @param ComparisonInterface|null $comparison  Comparison algorithm
     */
    public function __construct(ComparisonInterface $comparison = null)
    {
        $this->comparisonAlgorithm = $comparison;
    }

    /**
     * {@inheritdoc}
     */
    public function getLongestCommonSubsequence(array $sqnc1, array $sqnc2)
    {
        $sqnc1Length = count($sqnc1);
        $sqnc2Length = count($sqnc2);

        $result = array();
        $matrix = array_fill(0, (($sqnc1Length + 1) * ($sqnc2Length + 1)), 0);

        // Calculate LCS length matrix
        for ($i = 1; $i <= $sqnc1Length; $i++) {
            for ($j = 1; $j <= $sqnc2Length; $j++) {
                $current = ($j * ($sqnc1Length + 1)) + $i;

                $matchWeight = 0;
                if (($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($sqnc1[$i - 1], $sqnc2[$j - 1])) ||
                    ($this->comparisonAlgorithm === null && $sqnc1[$i - 1] === $sqnc2[$j - 1])) {
                    $matchWeight = $matrix[$current - $sqnc1Length - 2] + 1;
                }

                $matrix[$current] = max(
                    $matrix[$current - 1],
                    $matrix[$current - $sqnc1Length - 1],
                    $matchWeight
                );
            }
        }

        $i = $sqnc1Length;
        $j = $sqnc2Length;

        // Read out the LCS
        while ($i > 0 && $j > 0) {
            if (($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($sqnc1[$i - 1], $sqnc2[$j - 1])) ||
                ($this->comparisonAlgorithm === null && $sqnc1[$i - 1] === $sqnc2[$j - 1])) {
                array_unshift($result, $sqnc1[$i - 1]);
                $i--;
                $j--;
            } else {
                $current = ($j * ($sqnc1Length + 1)) + $i;
                if ($matrix[$current - 1] < $matrix[$current - $sqnc1Length - 1]) {
                    $j--;
                } else {
                    $i--;
                }
            }
        }

        return $result;
    }
}
