Custom comparison
=================

By implementing ``CHItA\PHPDiff\Comparison\ComparisonInterface``, you may add a custom data comparison implementation
to the algorithm. This could be useful if you would like to compare the trimmed versions of the units for example.

.. warning::
    Comparison algorithm never alters the content of the passed elements. Any manipulation of the input data in the
    comparison algorithm will not be present in the output.

However, please be aware that when you use this option, the returned units from two-way diffs will contain the units
from the modified version (second parameter of the Differ::diff() method). Please also note, that when using custom
comparisons with three-way diffs, the units in the output could be from either of the modified documents.

Example
^^^^^^^

An example comparison algorithm that trims whitespaces from the end of the units before comparing them.

.. code-block:: php

    use CHItA\PHPDiff\Comparison\ComparisonInterface;

    class TrimCompare implements ComparisonInterface
    {
        public function compare($value1, $value2)
        {
        	return rtrim($value1) === rtrim($value2);
        }
    }

.. code-block:: php

    use CHItA\PHPDiff\DifferBase;
    use CHItA\PHPDiff\Differ;

    $differ = new Differ();
    $differ->setComparisonAlgorithm(new TrimCompare());
    $diff = $differ->diff(
        array('a', 'b', 'b', 'c'),
        array(' a', 'b  ', 'b   ', 'c')
    );

    // $diff will contain an array with the following structure:
    //
    // array(
    //     array(
    //          array(
    //              'type'  => DifferBase::REMOVED,
    //              array('a')
    //          ),
    //          array(
    //              'type'  => DifferBase::ADDED,
    //              array(' a')
    //          )
    //     ),
    //     array(
    //          'type'  => DifferBase::UNCHANGED,
    //          array('b  ', 'b   ', 'c')
    //     )
    // )
