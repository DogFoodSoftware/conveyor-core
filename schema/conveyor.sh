#!/bin/bash

if [ ! -z ${stdenv+x} ]; then #if $stdenv set
    HOME="$home"
fi

source "$HOME/.conveyor/data/dogfoodsoftware.com/conveyor-core/odb-credentials"

cat <<EOF | orientdb-console
CONNECT remote://localhost/conveyor $ODB_USERNAME $ODB_PASSWORD

# As of 2.0-RC1, there's a bug such that the 'V' class does not exist
# until we idosynchratically cause it to pop into existence.

DELETE CLASS V

CREATE CLASS Profile EXTENDS V
ALTER CLASS Profile STRICTMODE true
CREATE PROPERTY Profile.surname STRING
CREATE PROPERTY Profile.given-name STRING

CREATE CLASS Agent EXTENDS V
ALTER CLASS Agent STRICTMODE true

CREATE CLASS User EXTENDS Agent
ALTER CLASS User STRICTMODE true
CREATE PROPERTY User.login-email STRING
CREATE PROPERTY User.profile LINK Profile
ALTER PROPERTY User.person MANDATORY true

quit
EOF
