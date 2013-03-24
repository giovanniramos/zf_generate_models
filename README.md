ZEND FRAMEWORK MODELS GENERATOR
======================

## About ##

Models Generator for the Zend Framework version 1.0.

This script aims to automate the creation and access of the database models.

To facilitate access to the elements of a data model, two abstract classes have been created and are available to perform this task.


## Installation ##

**Git clone**

    git clone git@github.com:giovanniramos/zf_generate_models.git

**Installation structure**

    <project name>/
    |-- application/
    |-- library/
    |   |-- App/
    |   |   `-- Model/
    |   |       |-- Mapper/
    |   |       |   `-- Abstract.php
    |   |       `-- Abstract.php
    |-- public/
    `-- scripts/
        `-- zf1_generator/

Copy the folder `zf1_generator` to the directory:

    <project name>/scripts/

Copy the folder `App` located in `zf1_library`, and paste in:

    <project name>/library

Add to your `application.ini` the following Namespaces:

    autoloaderNamespaces[] = "App"

To run the script, go through the url:

    <host name>/scripts/zf1_generator

That's all you need to do.


## License ##

Copyright (c) 2013 [Giovanni Ramos](https://github.com/giovanniramos)

Distributed under the [MIT License](http://www.opensource.org/licenses/MIT) (MIT-LICENSE.txt)