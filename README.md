
README {#mainpage}
==================

[TOC]

The Html Ascii Markup (HAM) library provides a simple framework for building
user interfaces from ASCII boxes.

This documentation has been generated by making use of Doxygen @cite doxygen.

Installation	{#install}
============

Dependencies	{#dependencies}
------------

HAM depends on the following programs and libraries available in standard repositories:

- A webserver, e.g., Apache
- PHP5
- GNU make (for developer documentation)
- bibtex (for developer documentation)
- doxygen (for developer documentation)
- links2 (optional text browser)

On Ubuntu systems the following command should install
most of the required dependencies:

	sudo apt-get install apache2 php5 libapache2-mod-php5 make doxygen links2

## Clone the HAM git repository {#clone} ##

	git clone TODO

## Test HAM installation (recommended) ##

Link the user documentation to your web directory and access it from your
favorite browser, for example:

	ln -s ham /var/www/html/ham
	links2 http://localhost/ham

## Build the developer documentation (optional) ##

Enter the newly cloned local copy and build the documentation:

	cd ham && make doc

Usage                   {#usage}
=====

Just include .... TODO

Makefile target list	{#targets}
====================

| Target     | Action                   |
| -------    | -------                  |
| all        | Build all                |
| doc        | Developer documentation  |
| clean      | Remove temporary files   |
| dist-clean | Remove all Make products |

Directory structure	{#structure}
===================

The HAM root directory has the following subdirectories with special meaning:

Directory | Description
--------- | -----------
css       | CSS style files
doc       | Documentation
examples  | Simple example projects
src       | Source code

Contribute		{#contribute}
==========

Contributions are welcome at all levels. Especially security related testing
and hardening is very desired.

If you want to contribute please [contact me](#contact).

Bug reports             {#bugreports}
-----------

You can find a list of all reported (and open) bugs [here](@ref buglist).

Please make sure that the browser you are using is on the
[list of fully supported browsers](#browsers) before submitting a bug report.

You can use the [bug report form](#bugform) for submitting unexpected behavior,
failing, bad, and/or dangerous code.

If that is not an option for you, please mail your bug report to the
[buglist](#buglist).

### Form                {#bugform}

TODO

### Email               {#bugmail}

Mail to: bugs@commandlinebeauty.com

The email should obey the following rules:

* the subject must start with "HAM - " (without quotes)
* followed by a short but meaningful bug title (example: "HAM - unintended extra char at the right side of rendered layout")
* omit greeting phrases but end the mail with an empty line followed by some name
* start the mail with a short description of the problem followed by an empty line
* include a line (enclosed by empty lines) stating your browser and its version in the following format:
	Browser: Firefox (56.0)
* attach files for reproducing the bug (use exactly these filenames):
	- index.php: The main file producing the bug
	- content.txt: The text file loaded by index.php

Your mail address as well as the content of your bug report might be publicly
visible. Please use the [bug report form](#bugform) if the former is not desired.

About			{#about}
=====

This Html Ascii Markup parser has been developed from a need of a framework
for creating simple user interfaces in very short time. TODO

License                 {#license}
-------

This program is free software: you can redistribute it and/or modify     
it under the terms of the GNU Affero General Public License as published 
by the Free Software Foundation, either version 3 of the License, or     
(at your option) any later version.                                      
                                                                         
This program is distributed in the hope that it will be useful,          
but WITHOUT ANY WARRANTY; without even the implied warranty of           
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            
GNU Affero General Public License for more details.                      
                                                                         
You should have received a
[copy of the GNU Affero General Public License](@ref agpl)
along with this program. If not, see <http://www.gnu.org/licenses/>.    

Contact			{#contact}
-------

For HAM related issues (please use the [buglist](#buglist) for bug reports):
	dev@commandlinebeauty.com

For general requests:
	root@commandlinebeauty.com

