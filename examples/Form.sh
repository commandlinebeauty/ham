#!/bin/bash
##!
##! @brief Example bash script
##! @author Fritz-Walter Schwarm
##!
##! Bash script for handling FORM type box submit requests

value=""

if [ $# -eq 1 ]
then
	##! One command line argument means:
	##! Echo the value of the given attribute or an error message
	##! Return 0 on success or any other value for failure
	if [ "$1" = "foo" ]
	then
		value="bar"
		echo "Echo value of \"$1\" -> \"$value\"" >> cgi.log
		echo $value
		exit 0;
	else
		value=""
		echo "Failed to obtain value for \"$1\"!" | tee -a cgi.log
		exit -1;
	fi

elif [ $# -eq 2 ]
then
	##! Two command line arguments mean:
	##! Set the attribute (Argument 1) to some value (Argument 2)
	##! Echo the error message on failure (return != 0)
	if [ "$1" = "foo" ]
	then
		echo "Set \"$1\" to \"$2\"" | tee -a cgi.log
		exit 0;
	fi
else
	echo "Usage: $0 <Name> [<Value>]" | tee -a cgi.log
	exit -1;
fi
