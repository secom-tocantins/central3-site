#!/bin/bash

function setup
{
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --prefer-dist
}

function server
{
    php -S 0.0.0.0:8000 -t public/
}

function init
{
    docker pull ubuntu:16.04
    build
}

function build
{
    docker build -t websecom-silex .
    run
}

function run
{
    docker run -p 8000:80 -v $(pwd):/var/www -ti websecom-silex
}

$1
