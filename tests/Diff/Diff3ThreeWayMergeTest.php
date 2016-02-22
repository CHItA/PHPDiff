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

use CHItA\PHPDiff\Diff3Algorithm\ThreeWayMerge;
use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\Differ3;
use CHItA\PHPDiff\DifferBase;
use CHItA\PHPDiff\LongestCommonSubsequence\Algorithm\Hirschberg;
use PHPUnit_Framework_TestCase;

class Diff3ThreeWayMergeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Differ3
     */
    private $differ;

    public function setUp()
    {
        $this->differ = new Differ3(
            new ThreeWayMerge(new Differ(null, new Hirschberg()))
        );
    }

    /**
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
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'e'),
                array('a', 'q', 'r', 's', 'b', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('q', 'r', 's')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('b')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('c', 'd', 'e', 'f')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('w', 'x', 'y')
                        )
                    ),
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'e', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('w', 'x', 'y', 'z')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array(),
                        array('e')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'e', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('w', 'x', 'y')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('z'),
                        array('e')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f', 'a', 'g', 'h'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f', 'a', 'j', 'k'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('c', 'd', 'e')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('w', 'x', 'y', 'z')
                        )
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('g', 'h'),
                        array('j', 'k')
                    )
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'q', 'f', 'p', 'p', 's'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('w', 'x', 'y')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('z'),
                        array('q')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('p', 'p', 's')
                    ),
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'q', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('w', 'x', 'y', 'z')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('c', 'd', 'e'),
                        array('q')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                ),
            ),
            array(
                array('a', 'b', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'c', 'd', 'e', 'f'),
                array('a', 'b', 'w', 'x', 'y', 'z', 'f'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('w', 'x', 'y', 'z')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('c', 'd', 'e'),
                        array()
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                ),
            ),
        );
    }
}
