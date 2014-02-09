#!/bin/bash

##### TO USE THIS FILE:
##
## 1) Copy to root of project home directory.
## 2) Find and change all instances of 'CHANGEME' in the text.
## 3) Remove this notice.
## 4) Implement project specific implementation logic, including but not
##    limited to the 'IMPLEMENTME' notes.
## 5) Ensure all instances of 'IMPLEMENTME' and 'CHANGEME' have been removed.

# /**
# * <div id="Overview" class="blurbSummary grid_12">
# * <div class="p">
# *   Conveyor compatible install script for CHANGEME. Based on the <a
# *   href="/documentation/conveyor/ref/code-templates/conveyor-install.sh">standard
# *   template</a>. The <a
# *   href="/documentation/conveyor-core/ref/Projects#Custom-Installation">installation
# *   script</a> CHANGEME(explain why the script is necessary and what it does
# *   beyond the <a
# *   href="/documentation/conveyor-core/ref/Projects#Standard-Installation">standard
# *   installation</a>). This script is designed to comply with the <a
# *   href="/documentation/conveyor-core/ref/Projects#install-script-requirements">common
# *   install script requirements</a>.
# * </div>
# * </div><!-- #Overview.blurbSummary -->
# * <div id="Implementation" class="blurbSummary grid_12">
# * <div class="blurbTitle">Implementation</div>
# * <div class="p">
# /**
# * <div class="p">
# *   Activate 'quick fail'. Kills scritp on any non-zero return. Some commands
# *   return non-erroneous non-zero results. These cases should be surrounded
# *   by <code>set +e</code>/<code>set -e</code> and the script as a whole
# *   should default to the quick fail mode.
# * </div>
# * <div class="p">
# *   It may be convenient to disable this for testing. In that case, be sure to
# *   re-set the value before generating any pull request.
# * </div>
set -e

# /**
# * <div class="p">
# *   Standard exit codes, as defined in the <a
# *   href="/documentation/conveyor-core/ref/Projects#install-script-requirements">common
# *   install script requirements</a>.
# * </div>
# */
EXIT_FAIL=1
EXIT_ALREADY_INSTALLED=11
EXIT_NO_STANDALONE_SUPPORT=12

source $HOME/.conveyor/config
source $DFS_HOME/conveyor/core/runnable/lib/shell-echo.sh
# Note the CHANGEME_HOME is set WRT the Conveyor configuration and the project
# itself, rather than extracting from $0.
CHANGEME_HOME=$CONVEYOR_PLAYGROUND/CHANGME(domain)/CHANGEME(project dir)
# CHANGEME_HOME=$DFS_HOME/CHANGEME(for DFS specific projects, OK to use shortcut)
if [ -d $CHANGME_HOME/runnable ]; then
    qerr "Looks like the project is already installed. Please remove the 'runnable' directory and try again."
    exit $EXIT_ALREADY_INSTALLED
fi
# * </div><!-- #Implementation.blurbSummary -->
# */
