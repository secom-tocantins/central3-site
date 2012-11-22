<?php
require_once __DIR__.'/../vendor/autoload.php';
Symfony\Component\HttpFoundation\Request::trustProxyData();

/* Central3 */
$mc = new \Memcached();
$mc->addServer('localhost', 11211);
$cache = new \Secom\Cache\Memcached($mc);
$client = new \Secom\Central3\Client('teste', $cache);

/* Silex */
$app = new Silex\Application();
$app['debug'] = true;

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => dirname(__DIR__).'/views'));
$twig = $app['twig'];

/*
* App
*/

/* Home */
$app->get('/', function () use ($twig) {
    return $twig->render('index.twig', array());
});

/* Listar notícias */
$listar = function () use ($twig, $client, $app) {
    $noticias = $client->byUri($app['request']->getPathInfo());
    return $twig->render('noticias.twig', array('noticias' => $noticias));
};
$app->get('/noticia/', $listar);
$app->get('/noticia/{ano}/', $listar);
$app->get('/noticia/{ano}/{mes}/', $listar);
$app->get('/noticia/{ano}/{mes}/{dia}/', $listar);

/* Visualizar notícia */
$app->get('/noticia/{ano}/{mes}/{dia}/{slug}/', function () use ($twig, $client, $app) {
    $noticia = $client->byUri($app['request']->getPathInfo());
    return $twig->render('noticia.twig', array('noticia' => $noticia));
});

/* Listar galerias */
$app->get('/galeria/', function () use ($twig, $client, $app) {
    $galerias = $client->byUri($app['request']->getPathInfo());
    return $twig->render('galerias.twig', array('galerias' => $galerias));
});

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function () use ($twig, $client, $app) {
    $galeria = $client->byUri($app['request']->getPathInfo());
    return $twig->render('galeria.twig', array('galeria' => $galeria));
});

/* Visualizar página */
$app->get('/{slug}/', function () use ($twig, $client, $app) {
    $pagina = $client->byUri($app['request']->getPathInfo());
    return $twig->render('pagina.twig', array('pagina' => $pagina));
});

$app->run();