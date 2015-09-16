function con_clone() {
    # We expect 'GITHUB_REF' in the form off 'DogFoodSoftware/conveyor-core'
    GITHUB_REF="$1"
    BRANCH="$2"
    if [[ ! -d $PLAYGROUND/$GITHUB_REF ]]; then
       pushd $PLAYGROUND > /dev/null
       sudo -u $CON_USER git clone -b $BRANCH --depth 1 git@github.com:${GITHUB_REF}.git $GITHUB_REF
       popd > /dev/null
    fi
}

function con_safe_mkdir {
    until [ -z $1 ]; do
       if [[ ! -d $1 ]]; then
           sudo -u $CON_USER mkdir $1
       fi
       shift
    done
}


function con_doc_link {
    local SOURCE_ROOT="$1"
    local PATH_OFFSET="$2"

    source /etc/environment # defines $DOCUMENTATION_HOME

    if [ ! -d "$DOCUMENTATION_HOME/$PATH_OFFSET" ]; then
	sudo -u $CON_USER mkdir "$DOCUMENTATION_HOME/$PATH_OFFSET"
    fi

    local SAVEIFS=$IFS
    IFS=$(echo -en "\n\b")

    for i in `ls $SOURCE_ROOT`; do
	IOFFSET="$PATH_OFFSET/$i"
	if [ -d "$SOURCE_ROOT/$i" ]; then
	    con_doc_link "$SOURCE_ROOT/$i" "$IOFFSET"
	else
	    if [ ! -f "$DOCUMENTATION_HOME/$IOFFSET" ]; then
		ln -s "$SOURCE_ROOT/$i" "${DOCUMENTATION_HOME}$IOFFSET"
	    fi
	fi
    done

    IFS=$SAVEIFS
}
