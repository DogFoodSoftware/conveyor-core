#!/bin/bash

source $HOME/.conveyor/config
source $CONVEYOR_HOME/runnable/lib/shell-echo.sh

RUNNABLE=$CONVEYOR_PLAYGROUND/$CONVEYOR_PROJECT_HOME/runnable

if [[ -d $RUNNABLE ]]; then
    rm -rf $RUNNABLE
    vecho "$PROJECT_NAME successfully uninstalled."
else
    vecho "$PROJECT_HOME does not appear to be installed."
fi
