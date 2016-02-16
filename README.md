#Central3 Client

#Instalação

## Crie o projeto
    curl -sS https://getcomposer.org/installer | php
    php composer.phar create-project secom-tocantins/central3-silex-project -s dev
    cd central3-silex-project

## Inicie o container
    Primeira execução: ./console init
    Nas demais execuções: ./console run

## Ou utilize o servidor Built-in do PHP
    ./console server
