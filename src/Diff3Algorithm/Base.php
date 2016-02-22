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

use CHItA\PHPDiff\ChunkProcessor;
use CHItA\PHPDiff\Comparison\ComparisonInterface;
use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\DifferBase;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\LCSInterface;
use CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface;
use InvalidArgumentException;

/**
 * Base class for three-way merge algorithms
 */
abstract class Base implements AlgorithmInterface
{
    use ChunkProcessor;

    /**
     * @var Differ
     */
    protected $differ;

    /**
     * Constructor
     *
     * @param Differ  $differ Differ algorithm
     */
    public function __construct(Differ $differ)
    {
        $this->differ = $differ;
    }

    /**
     * Sets the two way differ
     *
     * @param Differ  $algorithm
     */
    public function setDiffer(Differ $algorithm)
    {
        $this->differ = $algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function setLCSAlgorithm($algorithm)
    {
        if (!$algorithm instanceof LCSInterface && $algorithm !== null) {
            throw new InvalidArgumentException();
        }

        $this->differ->setLCSAlgorithm($algorithm);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComparisonAlgorithm($algorithm)
    {
        if (!$algorithm instanceof ComparisonInterface && $algorithm !== null) {
            throw new InvalidArgumentException();
        }

        $this->differ->setComparisonAlgorithm($algorithm);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLCSStrategy($strategy)
    {
        if (!$strategy instanceof StrategyInterface && $strategy !== null) {
            throw new InvalidArgumentException();
        }

        $this->differ->setLCSStrategy($strategy);

        return $this;
    }

    /**
     * Returns the common substring from the beginning of the blocks
     *
     * Side-effect: This function removes the leading substring from the sequences.
     *
     * @param array $block1 First block
     * @param array $block2 Second block
     *
     * @return array The common substring of the blocks
     */
    protected function getLeadingCommonSubstring(&$block1, &$block2)
    {
        $block = array();
        $comparison = $this->differ->getComparisonAlgorithm();

        while (isset($block1[0]) && isset($block2[0]) && (($comparison === null && $block1[0] === $block2[0]) ||
                ($comparison !== null && $comparison->compare($block1[0], $block2[0])))) {
            $block[] = array_shift($block1);
            array_shift($block2);
        }

        return $block;
    }

    /**
     * Resolves two diff blocks of different types
     *
     * @param array $block1 The first block
     * @param int   $type1  The type of the first block
     * @param array $block2 The second block
     * @param int   $type2  The type of the second block
     *
     * @return array The resulting diff block
     */
    protected function resolveBlock(&$block1, $type1, &$block2, $type2)
    {
        // Reduce the number possibilities from variations to combinations
        if ($type1 === DifferBase::UNCHANGED) {
            return $this->resolveBlock($block2, $type2, $block1, $type1);
        } elseif ($type1 === DifferBase::REMOVED && $type2 == DifferBase::ADDED) {
            return $this->resolveBlock($block2, $type2, $block1, $type1);
        }

        if ($type1 === DifferBase::REMOVED) { // $type2 === UNCHANGED
            return array('type' => $type1, $this->getLeadingCommonSubstring($block1, $block2));
        } else { // $type1 === DifferBase::ADDED
            // $type2 could either be UNCHANGED or REMOVED type, however that doesn't matter
            // as those line correspond to later blocks in the other diff, which will be resolved
            // then.
            $block = array('type' => $type1, $block1);
            $block1 = array();
            return $block;
        }
    }
}
