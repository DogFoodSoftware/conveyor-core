function con_doc_link {
    local SOURCE_ROOT="$1"
    local PATH_OFFSET="$2"

    local SAVEIFS=$IFS
    IFS=$(echo -en "\n\b")

    for i in `ls $SOURCE_ROOT`; do
	if [ -d "$SOURCE_ROOT/$i" ]; then
	    PATH_OFFSET="$PATH_OFFSET/$i"
	    if [ ! -d "$HOME/documentation/$i" ]; then
		mkdir "$HOME/documentation/$i"
	    fi
	    con_doc_link "$SOURCE_ROOT/$i" "$PATH_OFFSET"
	else
	    echo "is link"
	    IOFFSET="$PATH_OFFSET/$i"
	    if [ ! -f "$HOME/documentation/$IOFFSET" ]; then
		ln -s "$SOURCE_ROOT/$i" "$HOME/documentation$IOFFSET"
	    fi
	fi
    done

    IFS=$SAVEIFS
}
