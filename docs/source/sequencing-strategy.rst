Custom Sequencing strategy
==========================

By default, the diff algorithms expect arrays as the input, in which case it is assumed, that each element of the array
is a unit (an element of the sequence and a single letter of the alphabet which the sequence is generated from).

However, you can implement ``CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface`` and handle any type of input
yourself.

Example
^^^^^^^

In the example below, the sequence is generated from a string where the units are the bytes of the string (ascii
characters).

.. code-block:: php

    use CHItA\PHPDiff\SequencingStrategy\SequencingStrategyInterface;

    class MySequencer implements SequencingStrategyInterface
    {
        public function getSequence($dataSet)
        {
            return str_split($dataSet);
        }
    }
