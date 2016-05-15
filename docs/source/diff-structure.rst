Diff structure
==============

The library returns diffs in an array. These arrays contain the changes, which are grouped by the type of the changes
in the following format:

.. code-block:: php

    array(
        $change1,
        $change2,
    )

.. note::
    Where ``$changeN`` can be any of the change blocks listed below.

Diff change blocks
^^^^^^^^^^^^^^^^^^

**Unchanged block** is used when all of the two (or three) documents have the same units in common.

.. code-block:: php

   array(
       'type' => DifferBase::UNCHANGED,
       array('all', 'common', 'units', 'until', 'the', 'next', 'change type')
   )

**Added block** is used when the original version did not contain some units that are present in the modified version(s).

.. code-block:: php

   array(
       'type' => DifferBase::ADDED,
       array('all', 'common', 'units', 'until', 'the', 'next', 'change type')
   )

**Removed block** is used when the original version did contain some units that are not present in the modified version(s).

.. code-block:: php

   array(
       'type' => DifferBase::REMOVED,
       array('all', 'common', 'units', 'until', 'the', 'next', 'change type')
   )

**Edit block** is used when the modified version(s) contain both additions and deletions compared to the original version.

.. code-block:: php

   array(
      array(
         'type' => DifferBase::REMOVED,
         array('removed', 'units')
      ),
      array(
         'type' => DifferBase::ADDED,
         array('added', 'units')
      )
   )

**Conflict block** is used when a merge conflict cannot be resolved. This could only occur in three-way diffs. Note that
because these units are neither present nor removed from the document, in general no removed lines are returned before
this block (even where some units are removed from both modified versions).

.. code-block:: php

   array(
      'type' => DifferBase::CONFLICT,
      array('conflicting', 'units', 'in', 'version1'),
      array('conflicting', 'units', 'in', 'version2'),
   )
