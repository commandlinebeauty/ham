#!/bin/bash

if [ "$1" == "status" ]
then
	echo "undefined" | tee -a action.log
else
	echo "success" | tee -a action.log
fi
