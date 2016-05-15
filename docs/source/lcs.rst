Longest Common Subsequence
==========================

To generate diffs we solve the longest common subsequence problem for the documents to determine which lines are the ones
that did not changed.

This library provides two implementations out of the box for the longest common subsequence problem, one that is time
efficient (dynamic programming approach) and another that is memory efficient (Hirschberg's algorithm).

There is also an option to implement a strategy that selects the solver based on the inputs. For this, you need to
implement the ``CHItA\PHPDiff\LongestCommonSubsequence\Strategy\StrategyInterface``.
