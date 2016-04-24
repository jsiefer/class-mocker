# ClassMocker
[![Build Status](https://travis-ci.org/jsiefer/class-mocker.svg?branch=master)]
(https://travis-ci.org/jsiefer/class-mocker)
[![Coverage Status](https://coveralls.io/repos/github/jsiefer/class-mocker/badge.svg?branch=master)]
(https://coveralls.io/github/jsiefer/class-mocker?branch=master)


## Introduction

A simple helper library that lets you mock an entire frameworks or namespaces.
This is helpful for writing unit test for extensions or plugins for libraries that
do not support unit tests.

The idea is to automatically mock  entire namespaces. The classes are then generated
on the fly as soon as they are required.

You can register traits to add special functionality to certain classes if required.

Alternatively you can create a class footprint reference file that will old information
such as class hierarchy, interfaces or constants.


## Example

A simple example, imagine you are writing a plugin or extension for a framework
which does not support unit testing very well but it requires you to extend from classes
that are hard to mock without initializing the whole framework.

```php
/**
 * My awesome sample plugin
 */
class MyPlugin extends Example_Namespace_AbstractPlugin
{
    /**
     * Return full name
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->_isLoaded) {
            throw new Exception("Not yet loaded");
        }
        $firstname = $this->getFirstname();
        $lastname = $this->getLastname();

        return $firstname . ' ' . $lastname;
    }
}
```


Now lets assume in order to test the above method you need to initialize the entire framework which may take
a few seconds and defeats the purpose of quick unit tests.

The class-mocker lib lets you generate any missing class matching a pattern (e.g. ``Example_Namespace_*``)
which is then generated on the fly for you.

All generated class will also implementing the ``PHPUnit_Framework_MockObject_MockObject`` interface and give
you access to ``expects()`` and ``method()`` methods for testing.

To enable the class-mocker you need a custom bootstrap file for your PHPUnit test project and define the
classes that you want to generate on the fly.

```php
<?php
/**
 * Sample PHPUnit bootstrap.php
 */
include 'vendor/autoload.php';

$classMocker = new \JSiefer\ClassMocker\ClassMocker();
//$classMocker->mock('Example\Namespace\*');
$classMocker->mock('Example_Namespace_*');
$classMocker->enable();
```

That's it, now once enabled, you can simple test your classes without requiring any original framework classes.


```php
/**
 * Class MyPluginTest
 */
class MyPluginTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the getName method
     *
     * @return void
     * @test
     */
    public function testGetName()
    {
        /** @var MyPlugin|PHPUnit_Framework_MockObject_MockObject $myPlugin */
        $myPlugin = new MyPlugin();
        $myPlugin->_isLoaded = true;
        $myPlugin->expects($this->once())->method('getFirstname')->willReturn('John');
        $myPlugin->expects($this->once())->method('getLastname')->willReturn('Snow');

        $this->assertEquals(
            'John Snow',
            $myPlugin->getName(),
            'getName() Plugin did not return correct full name'
        );
    }

    /**
     * Should throw exception if object is not loaded
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Not yet loaded
     */
    public function shouldFailGetNameIfNotLoaded()
    {
        /** @var MyPlugin|PHPUnit_Framework_MockObject_MockObject $myPlugin */
        $myPlugin = new MyPlugin();
        $myPlugin->_isLoaded = false;

        // should throw Exception
        $myPlugin->getName();
    }
}
```



## Advanced

There is a lot more to it when you want to mock a framework, e.g. class hierarchies, class constants and some basic
classes.

The important part when mocking many classes, is that the whole PHPUnit Test needs to agree to the same implementation.

You can define class footprints and include JSON class footprint reference files for cloning an entire class tree.

Ideally you don't use this library directly but instead create a framework-mock library which provides all class
references and constants etc that are required and use that library in your project for testing.

This project was initial created to mock Magento for UnitTests. Check out the
[mock-mage lib](https://github.com/jsiefer/mage-mock) which uses this lib to mock the entire
Magento Framework/Application.
