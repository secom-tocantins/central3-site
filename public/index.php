<?php
require_once __DIR__.'/../vendor/autoload.php';
Symfony\Component\HttpFoundation\Request::trustProxyData();

/* Silex */
$app = new Silex\Application();
$app['debug'] = (getenv('APPLICATION_ENV') == 'development');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => dirname(__DIR__).'/views'));

/* Central3 */
$app['cache'] = $app->share(function() use ($app) {
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

/* Menu comum */
$app->before(function() use ($app) {
    $app['twig']->addGlobal('menu', $app['client']->query('pagina.listar'));
});

/*
* App
*/

/* Home */
$app->get('/', function (Silex\Application $app) {
    return $app['twig']->render('index.twig');
});

/* Listar notícias */
$listar = function (Silex\Application $app){
    try {
        $noticias = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('noticias.twig', array('noticias' => $noticias));
    }
    catch(\Exception $e) {
        $app->abort(404, $e->getMessage());
    }
};
$app->get('/noticia/', $listar);
$app->get('/noticia/{ano}/', $listar);
$app->get('/noticia/{ano}/{mes}/', $listar);
$app->get('/noticia/{ano}/{mes}/{dia}/', $listar);

/* Visualizar notícia */
$app->get('/noticia/{ano}/{mes}/{dia}/{slug}/', function (Silex\Application $app) {
    try {
        $noticia = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('noticia.twig', array('noticia' => $noticia));
    }
    catch(\Exception $e) {
        $app->abort(404, $e->getMessage());
    }
});

/* Listar galerias */
$app->get('/galeria/', function (Silex\Application $app) {
    try {
        $galerias = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galerias.twig', array('galerias' => $galerias));
    }
    catch(\Exception $e) {
        $app->abort(404, $e->getMessage());
    }
});

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function (Silex\Application $app) {
    try {
        $galeria = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galeria.twig', array('galeria' => $galeria));
    }
    catch(\Exception $e) {
        $app->abort(404, $e->getMessage());
    }
});

/* Visualizar página */
$app->get('/{slug}/', function (Silex\Application $app) {
    try {
        $pagina = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('pagina.twig', array('pagina' => $pagina));
    }
    catch(\Exception $e) {
        $app->abort(404, $e->getMessage());
    }
});

$app->error(function (\Exception $e, $code) use ($app) {
    return $app['twig']->render('error.twig', array('menu'=>$app['menu'], 'exception' => $e, 'code'=>$code));
});

$app->run();