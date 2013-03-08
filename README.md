ZF1 - MODELS GENERATOR
======================

## About ##

Models Generator for the Zend Framework version 1.0.

This script aims to automate the creation of the database models.


## Installation ##

**Git clone**

git clone https://github.com/giovanniramos/zf_generate_models.git

**Installation structure**

    <project name>/
    |   |
    |-- application/
    |   |
    |-- library/
    |   |
    |-- public/
    |   |
    `-- scripts/
        `-- zf1_generator/

Copy the folder `zf1_generator` to:

    <project name>/scripts/

Extract the files that are in `App.rar`, in directory:

    <project name>/library

Add to your `application.ini` the following Namespaces:

    autoloaderNamespaces[] = "App"

To run the script, go through the url:

    <host name>/scripts/zf1_generator

That's all you need to do.


## License ##

Copyright (c) 2013 [Giovanni Ramos](https://github.com/giovanniramos)

Distributed under the [MIT License](http://www.opensource.org/licenses/MIT) (MIT-LICENSE.txt)