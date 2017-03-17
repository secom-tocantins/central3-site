#!/bin/bash

function install
{
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --prefer-dist
}

function server
{
    php -S 0.0.0.0:8000 -t public/
}

$1
