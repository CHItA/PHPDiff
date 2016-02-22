<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\SequencingStrategy;

use InvalidArgumentException;

/**
 * Interface for sequencing strategies
 */
interface SequencingStrategyInterface
{
    /**
     * Returns an array containing the sequenced data
     *
     * @param mixed $dataSet    Data to sequence
     *
     * @return array Array of sequenced data
     *
     * @throws InvalidArgumentException When $dataSet has an unsupported type
     */
    public function getSequence($dataSet);
}
