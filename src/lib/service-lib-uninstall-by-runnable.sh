#!/bin/bash

source $HOME/.conveyor/config
source $CONVEYOR_HOME/runnable/lib/shell-echo.sh

RUNNABLE=$CONVEYOR_PLAYGROUND/$CONVEYOR_PROJECT_HOME/runnable

# TODO: we would love to use 'git check-ignore runnable/' here and cowardly
# refuse to delete unless there's a match. Implement '--force' option to skip
# check. The problem is 'check-ignore' was not implemented till git
# 1.8.2. Since we could potentially be on openSuSE 13.1 for awhile, there are
# other workarounds we could do till
# then. http://stackoverflow.com/questions/466764/show-ignored-files-in-git
if [[ -d $RUNNABLE ]]; then
    rm -rf $RUNNABLE
    vecho "$PROJECT_NAME successfully uninstalled."
else
    vecho "$PROJECT_HOME does not appear to be installed."
fi
