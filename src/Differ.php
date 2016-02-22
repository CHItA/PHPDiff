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

use InvalidArgumentException;

/**
 * Class for generating two-way diff
 */
final class Differ extends DifferBase
{
    /**
     * Generates the difference between two sequences
     *
     * When a parameter is passed as an array it is handled as if it were already sequenced.
     *
     * Please note, that a side effect of using a custom comparison object you will get back
     * the modified sequence's elements in the UNCHANGED diff blocks.
     *
     * @param mixed $original   The original data set
     * @param mixed $modified   The modified data set
     *
     * @return array Difference in array format
     *
     * @throws InvalidArgumentException When $original or $modified has an unsupported type
     */
    public function diff($original, $modified)
    {
        if (!is_array($original)) {
            $original = $this->getSequence($original);
        }

        if (!is_array($modified)) {
            $modified = $this->getSequence($modified);
        }

        $original   = array_values($original);
        $modified   = array_values($modified);

        $diff   = $this->getCommonLeadingLines($original, $modified);
        $end    = $this->getCommonTrailingLines($original, $modified);

        if ($this->LCSStrategy !== null) {
            $LCSAlgorithm = $this->LCSStrategy->getLCS($original, $modified);
        } else {
            $LCSAlgorithm = $this->LCSAlgorithm;
        }

        $LCSAlgorithm->setComparisonAlgorithm($this->comparisonAlgorithm);
        $lcs = $LCSAlgorithm->getLongestCommonSubsequence($modified, $original);

        for ($i = 0, $total = count($lcs); $i < $total; $i++) {
            $diff[] = $this->getEditBlock($original, $modified, $lcs[$i]);
            $diff[] = $this->getUnchangedBlock($original, $modified, $lcs, $i, $total);
        }

        $diff[] = $this->getDiffArray($modified, $original);

        if (!empty($end)) {
            $diff[] = $end;
        }

        return $diff;
    }

    /**
     * Returns common leading lines
     *
     * @param array &$original  Original sequence
     * @param array &$modified  Modified sequence
     *
     * @return array Array of the common leading lines
     */
    private function getCommonLeadingLines(&$original, &$modified)
    {
        $begin  = array();
        $min    = min(count($original), count($modified));

        for ($i = 0; $i < $min; $i++) {
            if (($this->comparisonAlgorithm === null && $original[0] === $modified[0]) ||
                ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($original[0], $modified[0]))) {
                $begin[] = array_shift($modified);
                array_shift($original);
            } else {
                break;
            }
        }

        return (empty($begin)) ? $begin : array(array('type' => DifferBase::UNCHANGED, $begin));
    }

    /**
     * Returns common trailing lines
     *
     * @param array &$original  Original sequence
     * @param array &$modified  Modified sequence
     *
     * @return array Array of the common trailing lines
     */
    private function getCommonTrailingLines(&$original, &$modified)
    {
        $revOrig    = array_reverse($original);
        $revMod     = array_reverse($modified);
        $min        = min(count($revOrig), count($revMod));
        $end        = array();

        for ($i = 0; $i < $min; $i++) {
            if (($this->comparisonAlgorithm === null && $revOrig[$i] === $revMod[$i]) ||
                ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($revOrig[$i], $revMod[$i]))) {
                array_unshift($end, array_pop($modified));
                array_pop($original);
            } else {
                break;
            }
        }

        return (empty($end)) ? $end : array('type'  => DifferBase::UNCHANGED, $end);
    }

    /**
     * Returns the diff block of the two sequences
     *
     * @param array     &$original  Original sequence
     * @param array     &$modified  Modified sequence
     * @param string    $nextLCS    The next element of the LCS to look for
     *
     * @return array Returns the diff block array
     */
    private function getEditBlock(&$original, &$modified, $nextLCS)
    {
        $added      = $this->getDifference($modified, $nextLCS);
        $removed    = $this->getDifference($original, $nextLCS);

        return $this->getDiffArray($added, $removed);
    }

    /**
     * Returns the difference between the block and the next element of the longest common subsequence
     *
     * @param array     &$block
     * @param string    $nextLCS
     *
     * @return array The elements in $block before the next element of the longest common subsequence
     */
    private function getDifference(&$block, $nextLCS)
    {
        $diff = array();

        while (($this->comparisonAlgorithm === null && $nextLCS !== $block[0]) ||
            ($this->comparisonAlgorithm !== null && !$this->comparisonAlgorithm->compare($nextLCS, $block[0]))) {
            $diff[] = array_shift($block);
        }

        return $diff;
    }

    /**
     * Returns the unchanged block
     *
     * @param array &$original  Original sequence
     * @param array &$modified  Modified sequence
     * @param array $lcs        Longest common subsequence
     * @param int   &$i         LCS array index
     * @param int   $total      Length of the LCS
     *
     * @return array Unchanged block
     */
    private function getUnchangedBlock(&$original, &$modified, $lcs, &$i, $total)
    {
        $j      = $i;
        $tmp    = array();

        do {
            array_shift($original);
            $tmp[] = array_shift($modified);
            $j++;
        } while ($j < $total &&
            (
                ($this->comparisonAlgorithm !== null && $this->comparisonAlgorithm->compare($lcs[$j], $original[0]) && $this->comparisonAlgorithm->compare($lcs[$j], $modified[0])) ||
                ($this->comparisonAlgorithm === null && $lcs[$j] === $original[0] && $lcs[$j] === $modified[0])
            )
        );

        $i = ($j < $total) ? $j - 1 : $j;

        return array('type' => DifferBase::UNCHANGED, $tmp);
    }

    /**
     * Returns the formated diff array from data arrays
     *
     * @param array $added      Array of added units
     * @param array $removed    Array of removed units
     *
     * @return array Returns the diff array
     */
    private function getDiffArray($added, $removed)
    {
        $added      = (!empty($added)) ? array('type' => DifferBase::ADDED, $added) : $added;
        $removed    = (!empty($removed)) ? array('type' => DifferBase::REMOVED, $removed) : $removed;

        if (!empty($removed) && !empty($added)) {
            return array(
                $removed,
                $added
            );
        } elseif (!empty($removed)) {
            return $removed;
        } else {
            return $added;
        }
    }
}
