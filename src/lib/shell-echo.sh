# /**
# * <div id="Overview" class="blurbSummary">
# *   Library providing drop in support for 'quiet' and 'verbose' output in
# *   shell scripts. <span data-perspective="coding">The script will enable
# *   the <code>-q/--quiet</code> and <code>-v/--verbose</code>
# *   options. Relies on the including script to invoke the argument
# *   processing prior to invoking the methods.</span>
#* </div><!-- #Overview.blurbSummary -->
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
