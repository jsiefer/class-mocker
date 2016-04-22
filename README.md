# ClassMocker 
[![Build Status](https://travis-ci.org/jsiefer/class-mocker.svg?branch=master)](https://travis-ci.org/jsiefer/class-mocker)
[![Coverage Status](https://coveralls.io/repos/github/jsiefer/class-mocker/badge.svg?branch=master)](https://coveralls.io/github/jsiefer/class-mocker?branch=master)

A simple helper library that lets you mock an entire frameworks or namespaces. 
This is helpful for writing unit test for extensions or plugins for libraries that
do not support unit tests.

The idea is to automatically mock  entire namespaces. The classes are then generated
on the fly as soon as they are required.

You can register traits to add special functionality to certain classes if required.

Alternatively you can create a class footprint reference file that will old information
such as class hierarchy, interfaces or constants.
