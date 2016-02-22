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
 * Abstract base class for calculating the longest common subsequence
 */
abstract class Base implements LCSInterface
{
    /**
     * @var ComparisonInterface
     */
    protected $comparisonAlgorithm;

    /**
     * {@inheritdoc}
     */
    public function setComparisonAlgorithm($algorithm)
    {
        if (!$algorithm instanceof ComparisonInterface && $algorithm !== null) {
            throw new InvalidArgumentException();
        }

        $this->comparisonAlgorithm = $algorithm;

        return $this;
    }

    /**
     * in_array() replacement in case comparison algorithm is set
     *
     * @param mixed $needle     Value to search for in the array
     * @param array $haystack   Array to search
     *
     * @return bool True if the array contains $needle, otherwise false
     */
    protected function inArray($needle, array $haystack)
    {
        if ($this->comparisonAlgorithm === null) {
            return in_array($needle, $haystack);
        } else {
            foreach ($haystack as $val) {
                if ($this->comparisonAlgorithm->compare($needle, $val)) {
                    return true;
                }
            }

            return false;
        }
    }
}
