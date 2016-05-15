Custom comparison
=================

By implementing ``CHItA\PHPDiff\Comparison\ComparisonInterface``, you may add a custom data comparison implementation
to the algorithm. This could be useful if you would like to compare the trimmed versions of the units for example.

However, please be aware that when you use this option, the returned units from two-way diffs will contain the units
from the modified version (second parameter of the Differ::diff() method). Please also note, that when using custom
comparisons with three-way diffs, the units in the output could be from either of the modified documents.
