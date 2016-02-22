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
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;
use CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface;
use CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface;
use InvalidArgumentException;

/**
 * Base class for diff generation classes
 */
abstract class DifferBase
{
    /**
     * Constant for unchanged units
     *
     * @var int
     */
    const UNCHANGED = 1;

    /**
     * Constant for added units
     *
     * @var int
     */
    const ADDED = 2;

    /**
     * Constant for removed units
     *
     * @var int
     */
    const REMOVED = 3;

    /**
     * Constant for merge conflicts (only used for three-way diffs)
     *
     * @var int
     */
    const CONFLICT = 4;

    /**
     * Advanced comparison interface
     *
     * @var ComparisonInterface
     */
    protected $comparisonAlgorithm;

    /**
     * Longest Common Subsequence algorithm implementation
     *
     * @var LCSInterface
     */
    protected $LCSAlgorithm;

    /**
     * Sequencing algorithm for non-array inputs
     *
     * @var SequencingStrategyInterface
     */
    protected $sequencingStrategy;

    /**
     * Three-way merge algorithm
     *
     * @var AlgorithmInterface
     */
    protected $algorithm;

    /**
     * LCS implementation selector
     *
     * @var StrategyInterface
     */
    protected $LCSStrategy;

    /**
     * Constructor
     *
     * @param StrategyInterface|null            $LCSstrategy        Longest common subsequence selecting strategy
     * @param LCSInterface|null                 $lcs                Longest common subsequence algorithm
     * @param SequencingStrategyInterface|null  $sequencingStrategy Non-array input sequencing strategy
     * @param ComparisonInterface|null          $comparison         Comparison algorithm
     */
    public function __construct(
        StrategyInterface $LCSstrategy = null,
        LCSInterface $lcs = null,
        SequencingStrategyInterface $sequencingStrategy = null,
        ComparisonInterface $comparison = null
    ) {
        $this->comparisonAlgorithm  = $comparison;
        $this->algorithm            = null;
        $this->sequencingStrategy   = $sequencingStrategy;

        if ($lcs === null) {
            $this->LCSAlgorithm = new Hirschberg($this->comparisonAlgorithm);
        } else {
            $this->LCSAlgorithm = $lcs;
            $this->LCSAlgorithm->setComparisonAlgorithm($this->comparisonAlgorithm);
        }
    }

    /**
     * Set the Longest Common Subsequence Algorithm to use
     *
     * @param LCSInterface|null  $algorithm
     *
     * @return Differ|Differ3 Returns the Diff object
     */
    public function setLCSAlgorithm($algorithm)
    {
        if (!$algorithm instanceof LCSInterface && $algorithm !== null) {
            throw new \InvalidArgumentException();
        }

        $this->LCSStrategy = null;
        $this->LCSAlgorithm = $algorithm;

        if ($this->algorithm !== null) {
            $this->algorithm->setLCSAlgorithm($algorithm);
        }

        return $this;
    }

    /**
     * Returns the longest common subsequence algorithm in use
     *
     * @return LCSInterface The longest common subsequence algorithm in use
     */
    public function getLCSAlgorithm()
    {
        return $this->LCSAlgorithm;
    }

    /**
     * Sets the sequencing algorithm for non-array inputs
     *
     * @param SequencingStrategyInterface $strategy Sequencing algorithm
     *
     * @return Differ|Differ3 Returns the Diff object
     */
    public function setDataSequencingStrategy(SequencingStrategyInterface $strategy)
    {
        $this->sequencingStrategy = $strategy;

        return $this;
    }

    /**
     * Returns the currently used sequencing algorithm
     *
     * @return SequencingStrategyInterface The currently used sequencing algorithm
     */
    public function getDataSequencingStrategy()
    {
        return $this->sequencingStrategy;
    }

    /**
     * Sets comparison algorithm
     *
     * @param ComparisonInterface|null $algorithm Comparison algorithm
     *
     * @return Differ|Differ3 Returns the Diff object
     */
    public function setComparisonAlgorithm($algorithm = null)
    {
        if (!$algorithm instanceof ComparisonInterface && $algorithm !== null) {
            throw new \InvalidArgumentException();
        }

        $this->comparisonAlgorithm = $algorithm;

        if ($this->algorithm !== null) {
            $this->algorithm->setComparisonAlgorithm($algorithm);
        }

        return $this;
    }

    /**
     * Returns comparison algorithm or null when non is used
     *
     * @return ComparisonInterface|null Comparison algorithm or null when non is used
     */
    public function getComparisonAlgorithm()
    {
        return $this->comparisonAlgorithm;
    }

    /**
     * Sets the LCS implementation selecting strategy
     *
     * @param StrategyInterface|null $strategy
     *
     * @return Differ|Differ3 Returns the Diff object
     */
    public function setLCSStrategy($strategy = null)
    {
        if (!$strategy instanceof StrategyInterface && $strategy !== null) {
            throw new \InvalidArgumentException();
        }

        $this->LCSAlgorithm = null;
        $this->LCSStrategy = $strategy;

        if ($this->algorithm !== null) {
            $this->algorithm->setLCSStrategy($strategy);
        }

        return $this;
    }

    /**
     * Returns the LCS implementation selecting strategy
     *
     * @return StrategyInterface The LCS implementation selecting strategy
     */
    public function getLCSStrategy()
    {
        return $this->LCSStrategy;
    }

    /**
     * Returns the sequenced data
     *
     * @param mixed $input  Data to be sequenced
     *
     * @return array Sequenced data
     *
     * @throws InvalidArgumentException When no sequencing strategy is set
     */
    protected function getSequence($input)
    {
        if ($this->sequencingStrategy === null) {
            throw new InvalidArgumentException();
        }

        return $this->sequencingStrategy->getSequence($input);
    }
}
