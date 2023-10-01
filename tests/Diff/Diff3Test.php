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

use CHItA\PHPDiff\Differ3;
use CHItA\PHPDiff\DifferBase;
use PHPUnit\Framework\TestCase;

class Diff3Test extends TestCase
{
    private static $reflectionMergeDiffMethod;

    private $differ;

    public static function setUpBeforeClass(): void
    {
        $reflection = new \ReflectionClass('\CHItA\PHPDiff\Differ3');
        self::$reflectionMergeDiffMethod = $reflection->getMethod('mergeOutput');
        self::$reflectionMergeDiffMethod->setAccessible(true);
    }

    public function setUp(): void
    {
        $this->differ = new Differ3();
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider mergeDiffDataProvider
     */
    public function testMergeDiff($data, $expected)
    {
        $data = self::$reflectionMergeDiffMethod->invoke($this->differ, $data);
        $this->assertEquals($expected, $data);
    }

    public function mergeDiffDataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('b')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('c')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('f', 'f')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('e', 'g')
                        )
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('d')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('h')
                    ),
                ),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('c')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('f', 'f', 'd')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('e', 'g', 'h')
                        )
                    )
                )
            ),
            array(
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('g', 'h', 'e'),
                        array('g', 'g', 'q', 'z')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                ),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::CONFLICT,
                        array('g', 'h', 'e'),
                        array('g', 'g', 'q', 'z')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('f')
                    ),
                )
            ),
            array(
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('f', 'f', 'd')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('e', 'g', 'h')
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        'type' => DifferBase::ADDED,
                        array('e', 'g', 'h')
                    ),
                    array(
                        'type' => DifferBase::REMOVED,
                        array('f', 'f', 'd')
                    ),
                ),
                array(
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('f', 'f', 'd')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('e', 'g', 'h')
                        ),
                    ),
                    array(
                        'type' => DifferBase::UNCHANGED,
                        array('a', 'b')
                    ),
                    array(
                        array(
                            'type' => DifferBase::REMOVED,
                            array('f', 'f', 'd')
                        ),
                        array(
                            'type' => DifferBase::ADDED,
                            array('e', 'g', 'h')
                        ),
                    ),
                )
            ),
        );
    }
}
