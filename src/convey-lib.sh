function mime_to_ext_find_match() {
    MIME_ACCEPT="$1"

    case "$MIME_ACCEPT" in
	"text/html")
	    echo '-name "*.html"';;
	"text/plain")
	    echo '-name "*.txt"';;
	"image/png")
	    echo '-name "*.png"';;
	"imgage/jpeg")
	    echo '-name "*.jpg" -o -name "*.jpeg"';;
	"image/gif")
	    echo '-name "*.gif"';;
	"application/javascript")
	    echo '-name "*.js"';;
	"application/json")
	    echo '-name "*.json"';;
	"application/x-sh")
	    echo '-name "*.sh"';;
	*)
	    echo "Could not determine extension for MIME type '$ACCEPT'." >&2
	    exit 1
    esac
}
