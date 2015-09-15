function con_doc_link {
    local SOURCE_ROOT="$1"
    local PATH_OFFSET="$2"

    source /etc/environment # defines $DOCUMENTATION_HOME

    local SAVEIFS=$IFS
    IFS=$(echo -en "\n\b")

    for i in `ls $SOURCE_ROOT`; do
	if [ -d "$SOURCE_ROOT/$i" ]; then
	    PATH_OFFSET="$PATH_OFFSET/$i"
	    if [ ! -d "$DOCUMENTATION_HOME/$i" ]; then
		mkdir "$DOCUMENTATION_HOME/$i"
	    fi
	    con_doc_link "$SOURCE_ROOT/$i" "$PATH_OFFSET"
	else
	    echo "is link"
	    IOFFSET="$PATH_OFFSET/$i"
	    if [ ! -f "$DOCUMENTATION_HOME/$IOFFSET" ]; then
		ln -s "$SOURCE_ROOT/$i" "${DOCUMENTATION_HOME}$IOFFSET"
	    fi
	fi
    done

    IFS=$SAVEIFS
}
