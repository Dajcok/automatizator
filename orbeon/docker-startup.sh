#!/bin/bash

catalina.sh run &

tail -f /usr/local/logs/orbeon.log
