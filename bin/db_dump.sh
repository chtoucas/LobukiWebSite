#!/bin/sh

PROJECT_DIR=$(cd `dirname $0` && pwd)/..
OUT_FILE=${PROJECT_DIR}/tmp/lobuki.sql

mysqldump \
    -u lobukisticker -plobu3510 --opt lobukisticker > ${OUT_FILE}
#mysqldump --routines --no-create-info --no-data --no-create-db --skip-opt  \
#    -u root -p27182810 --opt lobuki > ./lobuki-sp.sql
