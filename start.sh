#!/bin/sh
# This script starts the PHP built-in server using Railway's PORT variable
php -S 0.0.0.0:$PORT -t .