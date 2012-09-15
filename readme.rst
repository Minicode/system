###################
Welcome to Minicode
###################

Minicode is a hyper-light PHP agile development framework, it provides a have minimum kernel and high-performance MVC pattern system.

Minicode design aim is to build the minimum size of the PHP system, this system only provide a safe perfect kernel, with good routing control and automatic loading mechanism, kernel does not provide any application class library and the third party components, make project code and the framework itself at the same time to achieve the most concise state.

Developers can according to the configuration file free adjustment system kernel parameters, but Minicode also follow convention over configuration of the agile style, recommend developers use the system default configuration items, because it is through good design from the results.

Using hyper-light core framework advantage is without redundant function and class library, has the good code organization structure at the same time will not sacrifice performance. High expansibility brings not only flexible fast iterative type development, can also running most of the application and even more complex business requirements.

*******************
Applicable Scene
*******************

-  Any Web application Rapid construction
-  Any website type project agile development
-  Cloud platform use hyper-light core framework
-  Crontab script using Model Controller pattern
-  Other PHP program based on the framework


*******************
Server Requirements
*******************

-  PHP version 5.3.0 or newer.

************
Install
************

To download the latest version of `Minicode <https://github.com/Minicode/system/zipball/master/>`_
core package, or direct Git clone Minicode system project, install  to any position.

************************
PHP Environment Variable
************************

In Linux, Unix, MacOSX etc system, PHP environment variable is default setting, if there is no configuration please set PHP environment variable

Windows default PHP without the environment variable, please hand through the:
My computer attribute - > advanced system Settings - > path environment variable - > add PHP /bin absolute path


****************************
Command Line
****************************

Hypothesis Minicode system core package placed in the current user directory, package name for "system" :

Check the current version number, to determine whether the success of the installation ?

::

    system/bin/mc --version

or simply::

    system/bin/mc -v

Usually in order to system safety and a Shared core package, Minicode system directory generally placed in other directory, manual Settings Minicode environment variable, for the following operation bring more convenience.

eg: /usr/local/minicode

    In the path environment variable to add  /usr/local/minicode/bin

This can be more simple to use the command line (all of the following are in this manner)

::

    mc --version

Other Minicode cli command can view the help documents

::

    mc --help

or simply::

    mc -h

*********************
Create New Project
*********************

In the current directory to create an application for "app" project

::

    mc app

This will generate a single "index.php" and "app" directory, contains some basic directory:

-  config
-  models
-  controllers
-  views

You can also in this directory continue to create another's sub application, such as "admin"

::

    mc admin

This will generate another single "admin.php" and "admin" directory

    Note: once created new applications,  change the core system package path can lead to project operation failure, at this moment need to manually modified single entry documents such as "index.php",  $system_path value will be changed to the correct path.


    You can also directly core package placed in the project, with the project packed together, because Minicode Hyper-light design, in fact to do so and no badly. Just do it your cli command line need to use the current directory core package "bin/mc" to operate.