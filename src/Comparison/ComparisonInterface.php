<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\Comparison;

/**
 * Interface for comparing two values.
 *
 * This comparison might be used to extend comparison of two values and determine whether they are equal. This might
 * be necessary when the values should be trimed or transformed in any other way when computing the diff.
 */
interface ComparisonInterface
{
    /**
     * Compares two values
     *
     * @param mixed $value1 First value
     * @param mixed $value2 Second value
     *
     * @return bool True if the two values are equal, otherwise false
     */
    public function compare($value1, $value2);
}
