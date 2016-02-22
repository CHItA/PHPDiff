<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff;

use CHItA\PHPDiff\Comparison\ComparisonInterface;
use CHItA\PHPDiff\Diff3Algorithm\AlgorithmInterface;
use CHItA\PHPDiff\Diff3Algorithm\WeaveMerge;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;
use CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface;
use CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface;
use InvalidArgumentException;

/**
 * Class for generating three-way diff
 */
final class Differ3 extends DifferBase
{
    /**
     * Constructor
     *
     * @param AlgorithmInterface|null           $algorithm          Three-way merge algorithm
     * @param StrategyInterface|null            $LCSstrategy        Longest common subsequence selecting strategy
     * @param LCSInterface|null                 $lcs                Longest common subsequence algorithm
     * @param SequencingStrategyInterface|null  $sequencingStrategy Non-array input sequencing strategy
     * @param ComparisonInterface|null          $comparison         Comparison algorithm
     */
    public function __construct(
        AlgorithmInterface $algorithm = null,
        StrategyInterface $LCSstrategy = null,
        LCSInterface $lcs = null,
        SequencingStrategyInterface $sequencingStrategy = null,
        ComparisonInterface $comparison = null
    ) {
        parent::__construct($LCSstrategy, $lcs, $sequencingStrategy, $comparison);

        if ($algorithm !== null) {
            $this->algorithm = $algorithm;
        } else {
            $this->algorithm = new WeaveMerge(
                new Differ($LCSstrategy, $lcs, $sequencingStrategy, $comparison)
            );
        }
    }

    /**
     * Returns a three-way diff
     *
     * When a parameter is passed as an array it is handled as if it were already sequenced.
     *
     * @param mixed $original   The original data set
     * @param mixed $modified1  A modified data set
     * @param mixed $modified2  An other modified data set
     *
     * @return array Difference in array format
     *
     * @throws InvalidArgumentException When $original, $modified1 or $modified2 has an unsupported type
     */
    public function diff($original, $modified1, $modified2)
    {
        if (!is_array($original)) {
            $original = $this->getSequence($original);
        }

        if (!is_array($modified1)) {
            $modified1 = $this->getSequence($modified1);
        }

        if (!is_array($modified2)) {
            $modified2 = $this->getSequence($modified2);
        }

        $diff   = $this->getCommonLeadingLines($original, $modified1, $modified2);
        $end    = $this->getCommonTrailingLines($original, $modified1, $modified2);

        $diff = array_merge($diff, $this->algorithm->diff($original, $modified1, $modified2));
        $diff = array_merge($diff, $end);
        unset($original, $modified1, $modified2, $end);

        $diff = $this->mergeOutput($diff);

        return $diff;
    }

    /**
     * Merge change blocks
     *
     * @param array &$diff  Diff blocks to merge
     *
     * @return bool True if merges were made, otherwise false
     */
    private function mergeOutput($diff)
    {
        do {
            $previous   = -1;
            $changed    = false;
            $output     = array();
            $size       = 0;

            foreach ($diff as $val) {
                if (isset($val['type'])) {
                    if ($val['type'] === $previous) {
                        $output[$size - 1][0] = array_merge($output[$size - 1][0], $val[0]);
                        $changed = true;
                    } elseif ($previous === -2 && ($val['type'] === DifferBase::ADDED || $val['type'] === DifferBase::REMOVED)) {
                        if ($val['type'] === DifferBase::ADDED) {
                            $output[$size - 1][1][0] = array_merge($output[$size - 1][1][0], $val[0]);
                            $changed = true;
                        } else {
                            $output[$size - 1][0][0] = array_merge($output[$size - 1][0][0], $val[0]);
                            $changed = true;
                        }
                    } else {
                        if ($val['type'] === DifferBase::ADDED && $previous === DifferBase::REMOVED) {
                            $output[$size - 1] = array(
                                $output[$size - 1],
                                $val
                            );

                            $changed = true;
                            $previous = -2;
                        } elseif ($val['type'] === DifferBase::REMOVED && $previous === DifferBase::ADDED) {
                            $output[$size - 1] = array(
                                $val,
                                $output[$size - 1]
                            );

                            $changed = true;
                            $previous = -2;
                        } else {
                            $output[] = $val;
                            $previous = $val['type'];
                            $size++;

                            // Prevent two conflict blocks from being merged
                            if ($val['type'] === DifferBase::CONFLICT) {
                                $previous = -1;
                            }
                        }
                    }
                } else {
                    $output[] = $val;
                    $previous = -2;
                    $size++;
                }
            }

            $diff = $output;
        } while ($changed);

        return $diff;
    }

    /**
     * Returns common leading lines
     *
     * @param array &$original  Original sequence
     * @param array &$modified1 First modified sequence
     * @param array &$modified2 Second modified sequence
     *
     * @return array Array of the common leading lines
     */
    private function getCommonLeadingLines(&$original, &$modified1, &$modified2)
    {
        $begin = array();

        for ($i = 0, $total = min(count($original), count($modified1), count($modified2)); $i < $total; $i++) {
            if (($this->comparisonAlgorithm === null && $original[0] === $modified1[0] && $original[0] === $modified2[0]) ||
                ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($original[0], $modified1[0]) && $this->comparisonAlgorithm->compare($original[0], $modified2[0]))) {
                $begin[] = array_shift($original);
                array_shift($modified1);
                array_shift($modified2);
            } else {
                break;
            }
        }

        return (!empty($begin)) ? array(array('type' => DifferBase::UNCHANGED, $begin)) : array();
    }

    /**
     * Returns common trailing lines
     *
     * @param array &$original  Original sequence
     * @param array &$modified1 First modified sequence
     * @param array &$modified2 Second modified sequence
     *
     * @return array Array of the common trailing lines
     */
    private function getCommonTrailingLines(&$original, &$modified1, &$modified2)
    {
        $end = array();

        $i = count($original) - 1;
        $j = count($modified1) - 1;
        $k = count($modified2) - 1;

        while (min($i, $j, $k) >= 0 && (($this->comparisonAlgorithm === null && $original[$i] === $modified1[$j] && $original[$i] === $modified2[$k]) ||
            ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($original[$i], $modified1[$j]) && $this->comparisonAlgorithm->compare($original[$i], $modified2[$k])))
        ) {
            array_unshift($end, array_pop($original));
            array_pop($modified1);
            array_pop($modified2);
            $i--;
            $j--;
            $k--;
        }

        return (!empty($end)) ? array(array('type' => DifferBase::UNCHANGED, $end)) : array();
    }
}
