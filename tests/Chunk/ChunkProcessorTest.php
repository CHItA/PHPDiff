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

use CHItA\PHPDiff\ChunkProcessor;
use CHItA\PHPDiff\DifferBase;
use PHPUnit\Framework\TestCase;

class ChunkProcessorTest extends TestCase
{
    /**
     * @var ChunkProcessor
     */
    private $chunkProcessor;

    public function setUp(): void
    {
        $this->chunkProcessor = $this->getObjectForTrait('\CHItA\PHPDiff\ChunkProcessor');
    }

    /**
     * @dataProvider getOriginalChunkTestData
     */
    public function testGetOriginalChunk($expected, $block)
    {
        $this->assertEquals($expected, $this->chunkProcessor->getOriginalChunk($block));
    }

    /**
     * @dataProvider getNewChunkTestData
     */
    public function testGetNewChunk($expected, $block)
    {
        $this->assertEquals($expected, $this->chunkProcessor->getNewChunk($block));
    }

    public function testGetAddedChunk()
    {
        $data = array('a', 'b', 'c');
        $this->assertEquals(array('type' => DifferBase::ADDED, $data), $this->chunkProcessor->getAddedChunk($data));

        $this->assertEquals(array(), $this->chunkProcessor->getAddedChunk(array()));
    }

    public function testGetRemovedChunk()
    {
        $data = array('a', 'b', 'c');
        $this->assertEquals(array('type' => DifferBase::REMOVED, $data), $this->chunkProcessor->getRemovedChunk($data));

        $this->assertEquals(array(), $this->chunkProcessor->getRemovedChunk(array()));
    }

    public function testGetEditChunk()
    {
        $dataAdded = array('a', 'b', 'c');
        $dataRemoved = array('d', 'e', 'f');
        $this->assertEquals(
            array(
                array('type' => DifferBase::REMOVED, $dataRemoved),
                array('type' => DifferBase::ADDED, $dataAdded)
            ),
            $this->chunkProcessor->getEditChunk($dataRemoved, $dataAdded)
        );

        $this->assertEquals(
            array('type' => DifferBase::REMOVED, $dataRemoved),
            $this->chunkProcessor->getEditChunk($dataRemoved, array())
        );

        $this->assertEquals(
            array('type' => DifferBase::ADDED, $dataAdded),
            $this->chunkProcessor->getEditChunk(array(), $dataAdded)
        );

        $this->assertEquals(
            array(),
            $this->chunkProcessor->getEditChunk(array(), array())
        );
    }

    public function testGetConflictChunk()
    {
        $data1 = array('a', 'b', 'c');
        $data2 = array('d', 'e', 'f');
        $this->assertEquals(
            array(
                'type' => DifferBase::CONFLICT,
                $data1,
                $data2
            ),
            $this->chunkProcessor->getConflictChunk($data1, $data2)
        );

        $this->assertEquals(
            array(
                'type' => DifferBase::CONFLICT,
                $data2,
                $data1
            ),
            $this->chunkProcessor->getConflictChunk($data1, $data2, true)
        );

        $this->assertEquals(
            array(
                'type' => DifferBase::CONFLICT,
                $data1,
                array()
            ),
            $this->chunkProcessor->getConflictChunk($data1, array())
        );

        $this->assertEquals(
            array(
                'type' => DifferBase::CONFLICT,
                array(),
                $data1
            ),
            $this->chunkProcessor->getConflictChunk(array(), $data1)
        );

        $this->assertEquals(
            array(),
            $this->chunkProcessor->getConflictChunk(array(), array())
        );
    }

    public function getOriginalChunkTestData()
    {
        // Order: expected result, data block
        return array(
            array(
                array('a', 'b', 'c'),
                array(
                    'type' => DifferBase::UNCHANGED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array('a', 'b', 'c'),
                array(
                    'type' => DifferBase::REMOVED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array(),
                array(
                    'type' => DifferBase::ADDED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array(),
                array(
                    'type' => DifferBase::CONFLICT,
                    array('a', 'b', 'c'),
                    array('c', 'd', 'f')
                )
            ),
            array(
                array('a', 'b', 'c'),
                array(
                    array('type' => DifferBase::REMOVED, array('a', 'b', 'c')),
                    array('type' => DifferBase::ADDED, array('d', 'e', 'f')),
                )
            )
        );
    }

    public function getNewChunkTestData()
    {
        // Order: expected result, data block
        return array(
            array(
                array(),
                array(
                    'type' => DifferBase::UNCHANGED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array(),
                array(
                    'type' => DifferBase::REMOVED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array('a', 'b', 'c'),
                array(
                    'type' => DifferBase::ADDED,
                    array('a', 'b', 'c')
                )
            ),
            array(
                array(),
                array(
                    'type' => DifferBase::CONFLICT,
                    array('a', 'b', 'c'),
                    array('c', 'd', 'f')
                )
            ),
            array(
                array('d', 'e', 'f'),
                array(
                    array('type' => DifferBase::REMOVED, array('a', 'b', 'c')),
                    array('type' => DifferBase::ADDED, array('d', 'e', 'f')),
                )
            )
        );
    }
}
