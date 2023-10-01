<?php
/*
 * This file is part of the PHPDiff package.
 *
 * (c) Máté Bartus <mate.bartus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace CHItA\PHPDiff\Test;

require_once __DIR__ . '/LCSTestBase.php';

use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;

class HirschbergTest extends LCSTestBase
{
    public function setUp(): void
    {
        $this->LCSAlgorithm = new Hirschberg();
    }
}
