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

DEFINE_boolean 'quiet' $FLAGS_FALSE 'Suppresses "merely informative" output.' 'q'
DEFINE_boolean 'verbose' $FLAGS_FALSE 'Generally makes scripts more chatty.' 'v'

function qecho() {
    # Don't be quiet?
    if [ $FLAGS_quiet -eq $FLAGS_FALSE ]; then
	echo "$1"
    fi
}

function vecho() {
    # Be noisy?
    if [ $FLAGS_verbose -eq $FLAGS_TRUE ]; then
	echo "$1"
    fi
}
