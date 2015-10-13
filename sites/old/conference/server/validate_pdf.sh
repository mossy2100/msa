#!/bin/sh

FILEID="$1"

umask 111
TMPFILE=$(mktemp -q -t -p "/var/spool/pitstop/Input Folder") || exit 1
chmod a+rw "${TMPFILE}"

cat - > "${TMPFILE}"

/tmp/validate_pdf_2.sh "${FILEID}" "${TMPFILE}" </dev/null &>/dev/null &
