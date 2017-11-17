#!/bin/bash

##! Randomly decide if this script fails
let number=$RANDOM%100

if [ $number -lt 50 ]
then
	echo "success" | tee -a action.log
	exit 0
else
	echo "failure" | tee -a action.log
	exit 1
fi


##! FWS TODO remove this since status probably does not make sense with simple action boxes
##! that is normally the user does not want the script to be called before the button is
##! clicked not even with $1 == 'status'.
#if [ "$1" == "status" ]
#then
#	echo "undefined" | tee -a action.log
#	exit 0
#else
#	echo "success" | tee -a action.log
#	exit 0
#fi
