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

use PHPUnit\Framework\TestCase;
use CHItA\PHPDiff\Differ;
use CHItA\PHPDiff\DifferBase;

class DiffTest extends TestCase
{
    /**
     * @var Differ
     */
    private $diff;

    public function setUp(): void
    {
        $this->diff = new Differ();
    }

    /**
     * @dataProvider    diffTestProvider
     */
    public function testDiff($original, $modified, $expected)
    {
        $this->assertEquals($expected, $this->diff->diff($original, $modified));
    }

    public function diffTestProvider()
    {
        return array(
            array(
                array('a', 'b', 'b', 'a', 'c', 'b', 'b', 'a'),
                array('a', 'b', 'a', 'c', 'c', 'c', 'c', 'a', 'd'),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('b')
                    ),
                    array(
                        'type'  => DifferBase::UNCHANGED,
                        array('a', 'c')
                    ),
                    array(
                        array(
                            'type'  => DifferBase::REMOVED,
                            array('b', 'b')
                        ),
                        array(
                            'type'  => DifferBase::ADDED,
                            array('c', 'c', 'c')
                        )
                    ),
                    array(
                        'type'  => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        'type'  => DifferBase::ADDED,
                        array('d')
                    )
                )
            ),
        );
    }
}
