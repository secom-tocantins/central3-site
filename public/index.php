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

$menu = $client->query('pagina.listar');

/* Home */
$app->get('/', function () use ($twig, $menu) {
    return $twig->render('index.twig', array('menu'=>$menu));
});

/* Listar notícias */
$listar = function () use ($twig, $menu, $client, $app) {
    $noticias = $client->byUri($app['request']->getPathInfo());
    return $twig->render('noticias.twig', array('menu'=>$menu, 'noticias' => $noticias));
};
$app->get('/noticia/', $listar);
$app->get('/noticia/{ano}/', $listar);
$app->get('/noticia/{ano}/{mes}/', $listar);
$app->get('/noticia/{ano}/{mes}/{dia}/', $listar);

/* Visualizar notícia */
$app->get('/noticia/{ano}/{mes}/{dia}/{slug}/', function () use ($twig, $menu, $client, $app) {
    $noticia = $client->byUri($app['request']->getPathInfo());
    return $twig->render('noticia.twig', array('menu'=>$menu, 'noticia' => $noticia));
});

/* Listar galerias */
$app->get('/galeria/', function () use ($twig, $menu, $client, $app) {
    $galerias = $client->byUri($app['request']->getPathInfo());
    return $twig->render('galerias.twig', array('menu'=>$menu, 'galerias' => $galerias));
});

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function () use ($twig, $menu, $client, $app) {
    $galeria = $client->byUri($app['request']->getPathInfo());
    return $twig->render('galeria.twig', array('menu'=>$menu, 'galeria' => $galeria));
});

/* Visualizar página */
$app->get('/{slug}/', function () use ($twig, $menu, $client, $app) {
    $pagina = $client->byUri($app['request']->getPathInfo());
    return $twig->render('pagina.twig', array('menu'=>$menu, 'pagina' => $pagina));
});

$app->run();