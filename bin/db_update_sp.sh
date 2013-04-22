#!/bin/sh

PROJECT_DIR=$(cd `dirname $0` && pwd)/..

cd ${PROJECT_DIR}/bin

mysql -u lobukisticker -plobu3510 --database=lobukisticker < ./db_sp.sql

cd -
