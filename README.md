# ClassMocker
A simple helper library that lets you mock an entire frameworks or namespaces. 
This is helpful for writing unit test for extensions or plugins for libraries that
do not support unit tests.

The idea is to automatically mock  entire namespaces. The classes are then generated
on the fly as soon as they are required.

You can register traits to add special functionality to certain classes if required.

Alternatively you can create a class footprint reference file that will old information
such as class hierarchy, interfaces or constants.
