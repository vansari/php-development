#!/usr/bin/env bash

PHP=$(which php)

echo "#!$PHP" | cat - tmpl/post-create-project.php > temp \
&& mv temp tmpl/post-create-project.php
