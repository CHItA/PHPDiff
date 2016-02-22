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
 * Class implementing Hirschberg's algorithm
 */
final class Hirschberg extends Base
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
        $countSqnc1 = count($sqnc1);
        $countSqnc2 = count($sqnc2);

        if ($countSqnc1 < 2) {
            if ($countSqnc1 === 1 && $this->inArray($sqnc1[0], $sqnc2)) {
                return array($sqnc1[0]);
            }

            return array();
        }

        $splitIndex = floor($countSqnc1 / 2);
        $sqnc1Part1 = array_slice($sqnc1, 0, $splitIndex);
        $sqnc1Part2 = array_slice($sqnc1, $splitIndex);
        $nwScore1   = $this->NWScore($sqnc1Part1, $sqnc2);
        $nwScore2   = $this->NWScore(array_reverse($sqnc1Part2), array_reverse($sqnc2));
        $maxIndex   = 0;
        $maxScore   = 0;

        for ($i = 0; $i <= $countSqnc2; $i++) {
            $score = $nwScore1[$i] + $nwScore2[$countSqnc2 - $i];

            if ($maxScore <= $score) {
                $maxScore = $score;
                $maxIndex = $i;
            }
        }

        $sqnc2Part1 = array_slice($sqnc2, 0, $maxIndex);
        $sqnc2Part2 = array_slice($sqnc2, $maxIndex);

        return array_merge(
            $this->getLongestCommonSubsequence($sqnc1Part1, $sqnc2Part1),
            $this->getLongestCommonSubsequence($sqnc1Part2, $sqnc2Part2)
        );
    }

    /**
     * Calculates the Needleman-Wunsch score matrix
     *
     * @param array $sqnc1
     * @param array $sqnc2
     *
     * @return array Array containing the last row Needleman-Wunsch score matrix
     */
    private function NWScore(array $sqnc1, array $sqnc2)
    {
        $currentRow = array_fill(0, count($sqnc2) + 1, 0);
        $countSqnc1 = count($sqnc1);
        $countSqnc2 = count($sqnc2);

        for ($i = 0; $i < $countSqnc1; $i++) {
            $previousRow = $currentRow;
            for ($j = 0; $j < $countSqnc2; $j++) {
                if (($sqnc1[$i] === $sqnc2[$j] && $this->comparisonAlgorithm === null) ||
                    ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($sqnc1[$i], $sqnc2[$j]))) {
                    $currentRow[$j + 1] = $previousRow[$j] + 1;
                } else {
                    $currentRow[$j + 1] = max($currentRow[$j], $previousRow[$j + 1]);
                }
            }
        }

        return $currentRow;
    }
}
