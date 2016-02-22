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

/**
 * A trait for processing chunks
 */
trait ChunkProcessor
{
    /**
     * Returns the block which is contained in the original version
     *
     * @param array $block  Diff block
     *
     * @return array The block which is contained in the original version
     */
    public function getOriginalChunk($block)
    {
        if (isset($block['type'])) {
            if ($block['type'] === DifferBase::UNCHANGED || $block['type'] === DifferBase::REMOVED) {
                return $block[0];
            }

            return array();
        }

        return $block[0][0];
    }

    /**
     * Returns the block which is was not in the original version
     *
     * @param array $block  Diff block
     *
     * @return array The block which is was not in the original version
     */
    public function getNewChunk($block)
    {
        if (isset($block['type'])) {
            if ($block['type'] === DifferBase::ADDED) {
                return $block[0];
            }

            return array();
        }

        return $block[1][0];
    }

    /**
     * Returns a diff structure of removed elements
     *
     * @param array $removed    Array of removed elements
     *
     * @return array Diff structure of removed elements
     */
    public function getRemovedChunk($removed)
    {
        if (!empty($removed)) {
            return array('type' => DifferBase::REMOVED, $removed);
        }

        return array();
    }

    /**
     * Returns a diff structure of added elements
     *
     * @param array $added    Array of added elements
     *
     * @return array Diff structure of added elements
     */
    public function getAddedChunk($added)
    {
        if (!empty($added)) {
            return array('type' => DifferBase::ADDED, $added);
        }

        return array();
    }

    /**
     * Returns a diff structure for edits
     *
     * @param array $added      Array of added units
     * @param array $removed    Array of removed units
     *
     * @return array An appropriate diff structure based on the provided added and removed data
     */
    public function getEditChunk($removed, $added)
    {
        $removed    = $this->getRemovedChunk($removed);
        $added      = $this->getAddedChunk($added);

        if (!empty($removed) && !empty($added)) {
            return array(
                $removed,
                $added
            );
        } elseif (!empty($removed)) {
            return $removed;
        } elseif (!empty($added)) {
            return $added;
        }

        return array();
    }

    /**
     * Returns a diff structure for conflicts
     *
     * @param array $block1 Data to put in the conflict block
     * @param array $block2 Data to put in the conflict block
     * @param bool  $flip   Whether or not to swap the order of the blocks
     *
     * @return array Diff structure for conflicts
     */
    public function getConflictChunk($block1, $block2, $flip = false)
    {
        if (empty($block1) && empty($block2)) {
            return array();
        }

        if ($flip) {
            return $this->getConflictChunk($block2, $block1);
        }

        return array(
            'type' => DifferBase::CONFLICT,
            $block1,
            $block2
        );
    }
}
