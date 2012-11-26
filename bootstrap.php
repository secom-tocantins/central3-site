<?php
require_once __DIR__.'/vendor/autoload.php';
Symfony\Component\HttpFoundation\Request::trustProxyData();

/* Silex */
$app = new Silex\Application();
$app['debug'] = (getenv('APPLICATION_ENV') == 'development');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));

/* Cache */
$app['cache'] = $app->share(function() use ($app) {
    return null; // desabilitado
    if ($app['debug']) {
        return null;    
    }
    $mc = new \Memcached();
    $mc->addServer('localhost', 11211);
    return new \Secom\Cache\Memcached($mc);
});

$app['client'] = $app->share(function() use ($app) {
    return new \Secom\Central3\Client('teste', $app['cache']);
});