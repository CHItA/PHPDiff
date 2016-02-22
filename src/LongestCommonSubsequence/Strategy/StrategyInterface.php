<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\LongestCommonSubsequence\Strategy;

use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;

/**
 * Interface for selecting the most appropriate LCS implementation based on the inputs
 */
interface StrategyInterface
{
    /**
     * Returns the most appropriate LCS implementation based on the inputs
     *
     * @param array $original   Original sequence
     * @param array $modified   Modified sequence
     *
     * @return LCSInterface The most appropriate LCS implementation
     */
    public function getLCS(array $original, array $modified);
}
