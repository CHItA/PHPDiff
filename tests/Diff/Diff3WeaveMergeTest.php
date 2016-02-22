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

use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\Differ3;
use CHItA\PHPDiff\DifferBase;
use CHItA\PHPDiff\Diff3Algorithm\WeaveMerge;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;
use PHPUnit_Framework_TestCase;

class Diff3WeaveMergeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Differ3
     */
    private $differ;

    public function setUp()
    {
        $this->differ = new Differ3(
            new WeaveMerge(new Differ(null, new Hirschberg()))
        );
    }

    /**
     * @param array $original
     * @param array $modified1
     * @param array $modified2
     * @param array $expected
     * @dataProvider diff3TestProvider
     */
    public function testDiff3($original, $modified1, $modified2, $expected)
    {
        $this->assertEquals($expected, $this->differ->diff($original, $modified1, $modified2));

        // Conflict block's order depends on the input param order
        $flip_conflict_expect = array();
        foreach ($expected as $row) {
            if (isset($row['type']) && $row['type'] === DifferBase::CONFLICT) {
                $flip_conflict_expect[] = array('type' => $row['type'], $row[1], $row[0]);
            } else {
                $flip_conflict_expect[] = $row;
            }
        }

        $this->assertEquals($flip_conflict_expect, $this->differ->diff($original, $modified2, $modified1));
    }

    public function diff3TestProvider()
    {
        return array(
            array(
                array('a', 'a', 'b', 'c', 'f', 'f', 'd'),
                array('a', 'a', 'b', 'e', 'e', 'c', 'f', 'f'),
                array('a', 'a', 'b', 'e', 'g', 'g', 'e', 'c', 'f', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('e', 'g', 'g', 'e')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('c', 'f', 'f')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('d')
                    )
                ),
            ),
            array(
                array('a', 'a', 'b', 'c', 'f', 'f', 'd'),
                array('a', 'a', 'b', 's', 's', 'c', 'f', 'f'),
                array('a', 'a', 'b', 'e', 'g', 'g', 'e', 'c', 'f', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'a', 'b')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('s', 's'),
                        array('e', 'g', 'g', 'e')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('c', 'f', 'f')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('d')
                    )
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('d', 'e', 'f', 'g'),
                array('a', 'b', 'c'),
                array(
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('a', 'b', 'c', 'd', 'e', 'f')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('g')
                        )
                    )
                ),
            ),
            array(
                array('a', 'z'),
                array('a', 'b', 'b', 'z'),
                array('a', 'c', 'c', 'z', 's'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('b', 'b'),
                        array('c', 'c')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('z')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('s')
                    )
                ),
            ),
            array(
                array('a', 'b', 'c'),
                array('d', 'e', 'a', 'b', 'c'),
                array('a', 'g', 'h'),
                array(
                    array(
                        'type' => DifferBase::ADDED,
                        array('d', 'e')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('b', 'c')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('g', 'h')
                        )
                    )
                )
            ),
            array(
                array('a', 'a', 'b', 'c', 'f', 'f', 'd'),
                array('a', 'a', 'b', 'e', 'e', 'd'),
                array('a', 'a', 'b', 'e', 'g', 'g', 'e', 'd'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'a', 'b')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('c', 'f', 'f')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('e', 'g', 'g', 'e')
                        )
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('d')
                    )
                ),
            ),
            array(
                array('a', 'a', 'b', 'c', 'f', 'f', 'd'),
                array('a', 'a', 'b', 'q', 'q', 'd'),
                array('a', 'a', 'b', 'z', 'z', 'z', 'z', 'd'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'a', 'b')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('q', 'q'),
                        array('z', 'z', 'z', 'z')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('d')
                    )
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e'),
                array('d', 'e', 'a', 'b', 'c', 'd', 'f', 'f', 'e'),
                array('g', 'h', 'c', 'f', 'f'),
                array(
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('d', 'e'),
                        array('g', 'h')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('c')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('d', 'e')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('f', 'f')
                        )
                    ),
                )
            ),
            array(
                array('a', 'b', 'c', 'd', 'e'),
                array('a', 'd', 'e'),
                array('a', 'h', 'g', 'f', 'e'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('b', 'c', 'd')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('h', 'g', 'f')
                        )
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('e')
                    ),
                )
            ),
            array(
                array('a', 'b', 'c', 'd', 'e'),
                array('a', 'h', 'c', 'g', 'f', 'e'),
                array('a', 'h', 'g', 'f', 'e'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('b', 'c', 'd')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('h', 'g', 'f')
                        )
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('e')
                    ),
                )
            ),
            array(
                array('a', 'b', 'c', 'd', 'e'),
                array('a', 'b', 'x', 'y', 'z', 'w'),
                array('a', 'x', 'y', 'z', 'e'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('b', 'c', 'd', 'e')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('x', 'y', 'z', 'w')
                        )
                    ),
                )
            ),
            array(
                array('a', 'b', 'c', 'd', 'e'),
                array('a', 'b', 'c', 'x', 'y', 'z', 'd', 'e'),
                array('a', 'b', 'c', 'x', 'y', 'z'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b', 'c')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('d', 'e')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('x', 'y', 'z')
                        )
                    ),
                )
            ),
        );
    }
}
