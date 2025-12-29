#!/bin/sh
nohup /usr/local/sphinxforchinese/bin/searchd -c /data/sphinx/sphinx.conf >> /tmp/sphinxforchinese.log 2>&1 &
