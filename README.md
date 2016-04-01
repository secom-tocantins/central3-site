#Central3 Client

#Instalação

## Crie o projeto
    curl -sS https://getcomposer.org/installer | php
    php composer.phar create-project secom-tocantins/central3-site -s dev
    cd central3-site

## Inicie o container
    Primeira execução: ./console init
    Nas demais execuções: ./console run

## Ou utilize o servidor Built-in do PHP
    ./console server
