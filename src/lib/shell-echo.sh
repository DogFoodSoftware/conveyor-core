# /**
# * <div id="Overview" class="blurbSummary">
# *   <div class="p">
# *   Library providing drop in support for 'quiet' and 'verbose' output in
# *   shell scripts. The basic ideas is computers generally like very quite
# *   scripts while humans prefer chatty scripts, and by using this library,
# *   you're setting up to write a script that supports that idea.
# *   </div>
# *   <div class="p" data-perspective="coding">
# *   The general idea is that scripts primarily run by humans&mdash;CLI
# *   enablers&mdash;need to be quieted when run by automated processes and
# *   scripts designed primarily for automated processes should become more
# *   chatty when run by a human. To implement that, include
# *   <code>shflags</code> in your code, include this script, and then <a
# *   href="https://code.google.com/p/shflags/wiki/Documentation10x">process
# *   the flag options</a>. This script will enable the
# *   <code>-q/--quiet</code> and <code>-v/--verbose</code> options. Then, use
# *   'qecho' and 'vecho' for output as appropriate. Generally, you will stil
# *   use <code>echo</code> for all "functional messages" which should be
# *   displayed no matter what.
# *   </div>
# * </div><!-- #Overview.blurbSummary -->
# */

while [[ $# > 0 ]]; do
    case "$1" in
        --quite|-q)
            SHELL_ECHO_QUITE=YES
            shift ;;
        --verbose|-v)
            SHELL_ECHO_VERBOSE=YES
            shift ;;
        *)
            SHELL_ECHO_PASSTHRU="$SHELL_ECHO_PASSTHRU $1"
            shift ;;
    esac
done

set -- $SHELL_ECHO_PASSTHRU

function qecho() {
    # Don't be quiet?
    if [[ $SHELL_ECHO_QUITE == YES ]]; then
	echo "$1"
    fi
}

function vecho() {
    # Be noisy?
    if [[ $SHELL_ECHO_VERBOSE == YES ]]; then
	echo "$1"
    fi
}

function qerr() {
    # Don't be quiet?
    if [[ $SHELL_ECHO_QUITE == YES ]]; then
	echo "$1" >&2
    fi
}

function verr() {
    # Be noisy?
    if [[ $SHELL_ECHO_VERBOSE == YES ]]; then
	echo "$1" >&2
    fi
}
