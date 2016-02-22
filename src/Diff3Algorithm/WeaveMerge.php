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

use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\DifferBase;

/**
 * Implements weave three-way merge
 */
final class WeaveMerge extends Base
{
    /**
     * Constructor
     *
     * @param Differ    $differ Differ algorithm
     */
    public function __construct(
        Differ $differ
    ) {
        parent::__construct($differ);
    }

    /**
     * {@inheritdoc}
     */
    public function diff(array $original, array $modified1, array $modified2)
    {
        $diff = array();

        $diff1  = $this->differ->diff($original, $modified1);
        $diff2  = $this->differ->diff($original, $modified2);

        unset($original, $modified1, $modified2);

        $i = $j = 0;
        while (isset($diff1[$i]) && isset($diff2[$j])) {
            if (isset($diff1[$i]['type']) && isset($diff2[$j]['type'])) {
                if ($diff1[$i]['type'] === $diff2[$j]['type']) {
                    $type = $diff1[$i]['type'];
                    $block = $this->resolveSameTypeBlock($diff1[$i][0], $diff2[$j][0], $type);
                } else {
                    $block = $this->resolveBlock($diff1[$i][0], $diff1[$i]['type'], $diff2[$j][0], $diff2[$j]['type']);
                }

                $diff[] = $block;

                if (empty($diff1[$i][0])) {
                    unset($diff1[$i]);
                    $i++;
                }

                if (empty($diff2[$j][0])) {
                    unset($diff2[$j]);
                    $j++;
                }
            } else {
                $diff[] = $this->resolveEdits($diff1, $diff2, $i, $j);
            }
        }

        if (isset($diff1[$i])) {
            $diff = array_merge($diff, $diff1);
        } elseif (isset($diff2[$j])) {
            $diff = array_merge($diff, $diff2);
        }

        return $diff;
    }

    /**
     * Resolves two diff blocks of the same type
     *
     * @param array $block1 First block
     * @param array $block2 Second block
     * @param int   $type   The type of the blocks
     *
     * @return array The resulting diff block
     */
    private function resolveSameTypeBlock(&$block1, &$block2, $type)
    {
        $conflict = false;

        if ($type === DifferBase::UNCHANGED || $type === DifferBase::REMOVED) {
            return array('type' => $type, $this->getLeadingCommonSubstring($block1, $block2));
        } else { // $type === DifferBase::ADDED
            if ($this->isSubsequence($block1, $block2)) {
                $block = (count($block1) > count($block2)) ? $block1 : $block2;
            } else {
                $block = $this->getConflictChunk($block1, $block2);
                $conflict = true;
            }

            $block1 = array();
            $block2 = array();

            return ($conflict) ? $block : $this->getAddedChunk($block);
        }
    }

    /**
     * Resolves blocks containing edits
     *
     * @param array &$diff1 First diff block
     * @param array &$diff2 Second diff block
     * @param int   &$i     Index for the first diff block
     * @param int   &$j     Index for the second diff block
     *
     * @return array Diff block
     */
    private function resolveEdits(&$diff1, &$diff2, &$i, &$j)
    {
        $added1         = array();
        $added2         = array();
        $depth1         = 0;
        $depth2         = 0;

        // One of the blocks must be an edit
        if (!isset($diff1[$i]['type'])) {
            $added1     = $this->getNewChunk($diff1[$i]);
            $removed    = $this->getOriginalChunk($diff1[$i]);
            $depth1     = count($removed);
            unset($diff1[$i]);
            $i++;
        } else {
            $added2     = $this->getNewChunk($diff2[$j]);
            $removed    = $this->getOriginalChunk($diff2[$j]);
            $depth2     = count($removed);
            unset($diff2[$j]);
            $j++;
        }

        // Align the bottom of the two diff blocks
        while ($depth1 !== $depth2) {
            if ($depth1 < $depth2) {
                $depth1 += $this->resolveEditBlock($removed, $added1, $diff1, $i, $depth2 - $depth1);
            } else {
                $depth2 += $this->resolveEditBlock($removed, $added2, $diff2, $j, $depth1 - $depth2);
            }
        }

        // Resolve conflicts
        if (empty($added1)) {
            return $this->getEditChunk($removed, $added2);
        } elseif (empty($added2)) {
            return $this->getEditChunk($removed, $added1);
        } else {
            if ($this->isSubsequence($added1, $added2)) {
                return $this->getEditChunk(
                    $removed,
                    ((count($added1) > count($added2)) ? $added1 : $added2)
                );
            } else {
                return $this->getConflictChunk(
                    $added1,
                    $added2
                );
            }
        }
    }

    /**
     * Resolves edit chunks
     *
     * @param array &$removed   Array of removed lines
     * @param array &$added     Array of added lines by the current diff block
     * @param array &$diff      Array containing the current diff block
     * @param int   &$i         Diff block array iterator
     * @param int   $depth      The number of units that the other block is ahead
     *
     * @return int The number of units that the current block got longer with
     */
    private function resolveEditBlock(&$removed, &$added, &$diff, &$i, $depth)
    {
        $original   = $this->getOriginalChunk($diff[$i]);
        $new        = $this->getNewChunk($diff[$i]);
        $size       = count($original);

        // Handle non-edit blocks
        if (isset($diff[$i]['type'])) {
            if ($diff[$i]['type'] === DifferBase::ADDED) { // Handle added lines
                $added = array_merge($added, $new);
            } elseif ($size > $depth) { // Handle removed or unchanged lines when the chunk shouldn't be removed
                $diff[$i][0] = array_slice($diff[$i][0], $depth);
                return $depth;
            }
        } else { // Handle edit blocks
            $added = array_merge($added, $new);

            if ($size > $depth) {
                $removed = array_merge($removed, array_slice($original, $depth));
            }
        }

        unset($diff[$i]);
        $i++;

        return $size;
    }

    /**
     * Returns whether one of the blocks is a subsequence of the other
     *
     * @param array $block1 First sequence
     * @param array $block2 Second sequence
     *
     * @return bool True if one of the blocks is a subsequence of the other, otherwise false
     */
    private function isSubsequence($block1, $block2)
    {
        if (count($block1) < count($block2)) {
            return $this->isSubsequence($block2, $block1);
        }

        $comparison = $this->differ->getComparisonAlgorithm();
        $j = 0;
        for ($i = 0, $total = count($block1); $i < $total; $i++) {
            if (!isset($block2[$j])) {
                return true;
            }

            if (($comparison === null && $block1[$i] === $block2[$j]) ||
                ($comparison !== null && $comparison->compare($block1[$i], $block2[$j]))) {
                $j++;
            }
        }

        return $j === count($block2);
    }
}
