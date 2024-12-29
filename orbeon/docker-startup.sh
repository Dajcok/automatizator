#!/bin/bash

export JAVA_OPTS="$JAVA_OPTS"
exec catalina.sh run &

tail -f /usr/local/logs/orbeon.log
