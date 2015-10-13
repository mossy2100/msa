#!/bin/sh

#
# you need to customize these!
#
DB_HOSTNAME='';
DB_DATABASE='';
DB_USERNAME='';
DB_PASSWORD='';
DB_PREFIX='';

#
# Pitstop server default values
#
INP="Input Folder"
NPDF_ERR="Non-PDF Error Logs"
NPDF="Non-PDF files"
ORIG_ERR="Original Docs on Failure"
ORIG_OK="Original Docs on Success"
PROC_ERR="Processed Docs on Failure"
PROC_OK="Processed Docs on Success"
REP_ERR="Reports on Failure"
REP_OK="Reports on Success"

#
# normally no need to change anything below
#

DEBUGLOG="/tmp/validate_pdf.log"
FILEID="$1"
TMPFILE="$2"
# TMPFILE was created in $INP_DIR
DIR=$(dirname "${TMPFILE}")/..
FILENAME=$(basename "${TMPFILE}")
#echo "$(date): $0 ${FILEID} ${TMPFILE}" >> "${DEBUGLOG}"

# wait for pitstop to process this file...
while [ -f "${TMPFILE}" ]; do
    sleep 5
done
#echo "pitstop has processed the pdf-file" >> "${DEBUGLOG}"

# ensure the result is present
while (! [ -f "${DIR}/${NPDF}/${FILENAME}" ]) &&
      (! [ -f "${DIR}/${ORIG_ERR}/${FILENAME}" ]) &&
      (! [ -f "${DIR}/${ORIG_OK}/${FILENAME}" ]); do
    sleep 5
done


LOGFILENAME="${FILENAME}_log.pdf"
TYPE=0

if [ -f "${DIR}/${NPDF}/${FILENAME}" ]; then
    #echo "not a pdf-file" >> "${DEBUGLOG}"
    TYPE=1
    REPNAME="${DIR}/${NPDF_ERR}/${LOGFILENAME}"
    VALID="false"
fi
if [ -f "${DIR}/${ORIG_ERR}/${FILENAME}" ]; then
    #echo "invalid pdf-file" >> "${DEBUGLOG}"
    TYPE=2
    REPNAME="${DIR}/${REP_ERR}/${LOGFILENAME}"
    VALID="false"
fi
if [ -f "${DIR}/${ORIG_OK}/${FILENAME}" ]; then
    #echo "VALID pdf-file" >> "${DEBUGLOG}"
    TYPE=3
    REPNAME="${DIR}/${REP_OK}/${LOGFILENAME}"
    VALID="true"
fi


#
# insert into database
# (you will need the global right "FILE")
#

# get date and time
DATETIME=$(/bin/ls -l "${REPNAME}" | cut -d' ' -f 7-8)
# get file size
FILESIZE=$(/bin/ls -l "${REPNAME}" | cut -d' ' -f 6)
# construct SQL
SQL="REPLACE INTO File_report (FileID,File,Valid,DateTime,FileSize) VALUES (${FILEID},LOAD_FILE('${REPNAME}'),${VALID},'${DATETIME}',${FILESIZE})"
#echo "SQL: ${SQL}" >> "${DEBUGLOG}"
echo "${SQL}" | mysql -h "${DB_HOSTNAME}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}"

# cleanup
rm "${DIR}/${NPDF}/${FILENAME}" "${DIR}/${ORIG_ERR}/${FILENAME}" "${DIR}/${ORIG_OK}/${FILENAME}" "${DIR}/${PROC_ERR}/${FILENAME}" "${DIR}/${PROC_OK}/${FILENAME}"
rm "${DIR}/${NPDF_ERR}/${LOGFILENAME}" "${DIR}/${REP_ERR}/${LOGFILENAME}" "${DIR}/${REP_OK}/${LOGFILENAME}"
