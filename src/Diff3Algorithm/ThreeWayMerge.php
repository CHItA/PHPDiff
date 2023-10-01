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
 * Implementation of the three-way merge algorithm
 */
final class ThreeWayMerge extends Base
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
        $result = array();

        $diff1 = $this->differ->diff($original, $modified1);
        $diff2 = $this->differ->diff($original, $modified2);

        unset($original, $modified1, $modified2);

        $i = $j = 0;
        while (isset($diff1[$i]) && isset($diff2[$j])) {
            if (isset($diff1[$i]['type']) && isset($diff2[$j]['type'])) {
                $result = array_merge(
                    $result,
                    $this->resolveNonEditBlock($diff1[$i][0], $diff1[$i]['type'], $diff2[$j][0], $diff2[$j]['type'])
                );

                if (empty($diff1[$i][0])) {
                    unset($diff1[$i]);
                    $i++;
                }

                if (empty($diff2[$j][0])) {
                    unset($diff2[$j]);
                    $j++;
                }
            } else {
                $result = array_merge($result, $this->resolveEdits($diff1, $i, $diff2, $j));
            }
        }

        if (isset($diff1[$i])) {
            $result = array_merge($result, $diff1);
        } elseif (isset($diff2[$j])) {
            $result = array_merge($result, $diff2);
        }

        return $result;
    }

    /**
     * Merges two non-edit diff blocks
     *
     * @param array &$block1    First diff block
     * @param int   $type1      Type of the first diff block
     * @param array &$block2    Second diff block
     * @param int   $type2      Type of the second diff block
     *
     * @return array The merged diff block
     */
    private function resolveNonEditBlock(&$block1, $type1, &$block2, $type2)
    {
        if ($type1 === $type2) {
            return $this->resolveSameTypeBlocks($block1, $block2, $type1);
        } else {
            return array($this->resolveBlock($block1, $type1, $block2, $type2));
        }
    }

    /**
     * Resolve edit blocks
     *
     * @param array &$diff1 First diff block
     * @param int   &$i     First diff block pointer
     * @param array &$diff2 Second diff block
     * @param int   &$j     Second diff block pointer
     *
     * @return array Merged diff block
     */
    private function resolveEdits(&$diff1, &$i, &$diff2, &$j)
    {
        $conflict       = false;
        $conflict1      = array();
        $conflict2      = array();
        $depth1         = 0;
        $depth2         = 0;

        // Bootstrap the alignment process
        // On of these blocks must be an edit
        if (!isset($diff1[$i]['type'])) {
            $removed    = $this->getOriginalChunk($diff1[$i]);
            $added      = $this->getNewChunk($diff1[$i]);
            $conflict1  = $added;
            $depth1     = count($removed);
            unset($diff1[$i]);
            $i++;
        } else {
            $removed    = $this->getOriginalChunk($diff2[$j]);
            $added      = $this->getNewChunk($diff2[$j]);
            $conflict2  = $added;
            $depth2     = count($removed);
            unset($diff2[$j]);
            $j++;
        }

        // Align the bottom of the blocks
        while ($depth1 !== $depth2) {
            if ($depth1 < $depth2) {
                $conflict = $this->resolveEditBlock(
                    $removed,
                    $conflict1,
                    $diff1,
                    $i,
                    $depth1,
                    $depth2 - $depth1
                ) || $conflict;
            } else {
                $conflict = $this->resolveEditBlock(
                    $removed,
                    $conflict2,
                    $diff2,
                    $j,
                    $depth2,
                    $depth1 - $depth2
                ) || $conflict;
            }
        }

        if (!$conflict) {
            return array($this->getEditChunk($removed, $added));
        }

        $common = $this->getLeadingCommonSubstring($conflict1, $conflict2);
        $diff = array();

        if (!empty($common)) {
            if (empty($conflict1) && empty($conflict2)) {
                return array($this->getEditChunk($removed, $common));
            }

            $diff[] = $this->getAddedChunk($common);
        }

        $diff[] = $this->getConflictChunk($conflict1, $conflict2);

        return $diff;
    }

    /**
     * Resolve edit blocks with any type of blocks
     *
     * @param array &$removed       Removed atoms
     * @param array &$added         Atoms that are present in the given copy
     * @param array &$diff          Diff blocks in the given copy
     * @param int   &$i             Index of the current active diff block in the given copy
     * @param int   &$depth         Depth of the current resolve process
     * @param int   $depthTarget    Target depth to go in the current resolve process
     *
     * @return bool Whether or not there is a possible conflict
     */
    private function resolveEditBlock(&$removed, &$added, &$diff, &$i, &$depth, $depthTarget)
    {
        $conflict       = false;
        $deleteChunk    = false;
        $original       = $this->getOriginalChunk($diff[$i]);
        $new            = $this->getNewChunk($diff[$i]);
        $size           = count($original);

        // Handle non-edit blocks
        if (isset($diff[$i]['type'])) {
            if ($diff[$i]['type'] === DifferBase::ADDED) {
                $added = array_merge($added, $new);
                $deleteChunk = true;
                $conflict = true;
            } else {
                if ($size > $depthTarget) {
                    $diff[$i][0] = array_slice($original, $depthTarget);
                    $depth += $depthTarget;
                } else {
                    $depth += $size;
                    $deleteChunk = true;
                }

                if ($diff[$i]['type'] === DifferBase::UNCHANGED) {
                    $added = array_merge($added, array_slice($original, 0, $depthTarget));
                }
            }
        } else { // Handle edit blocks
            $conflict = true;
            $deleteChunk = true;
            $added = array_merge($added, $new);
            $removed = array_merge($removed, array_slice($original, $depthTarget));
            $depth += $size;
        }

        if ($deleteChunk) {
            unset($diff[$i]);
            $i++;
        }

        return $conflict;
    }

    /**
     * Resolves two diff blocks of the same type
     *
     * @param array &$block1    First diff block
     * @param array &$block2    Second diff block
     * @param int   $type       Type of the diff blocks
     *
     * @return array Diff block
     */
    private function resolveSameTypeBlocks(&$block1, &$block2, $type)
    {
        $result = array();
        $common = $this->getLeadingCommonSubstring($block1, $block2);

        if (!empty($common)) {
            $result[] = array(
                'type' => $type,
                $common
            );
        }

        if ($type === DifferBase::ADDED) {
            if (!empty($block1) || !empty($block2)) {
                $result[] = $this->getConflictChunk($block1, $block2);
                $block1 = $block2 = array();
            }
        }

        return $result;
    }
}
