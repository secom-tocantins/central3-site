<?php

include('../bootstrap.php');

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Secom\Central3\Client\Exception\ApiException,
    Secom\Central3\Client\Exception\CommunicationException,
    \Exception;

/* Menu comum */
$app->before(function() use ($app) {
    $app['twig']->addGlobal('menu', $app['client']->query('pagina.listar'));
});

/* Home */
$app->get('/', function (Silex\Application $app) {
    return $app['twig']->render('index.twig');
});

/* Listar notícias */
$listar = function (Silex\Application $app){
    try {
        $noticias = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('noticias.twig', array('noticias' => $noticias));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
};
$app->get('/noticia/', $listar);
$app->get('/noticia/{ano}/', $listar);
$app->get('/noticia/{ano}/{mes}/', $listar);
$app->get('/noticia/{ano}/{mes}/{dia}/', $listar);

/* Visualizar notícia */
$app->get('/noticia/{ano}/{mes}/{dia}/{slug}/', function (Silex\Application $app) {
    try
    {
        $noticia = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('noticia.twig', array('noticia' => $noticia));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
});

/* Listar galerias */
$app->get('/galeria/', function (Silex\Application $app) {
    try {
        $galerias = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galerias.twig', array('galerias' => $galerias));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
});

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function (Silex\Application $app) {
    try {
        $galeria = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galeria.twig', array('galeria' => $galeria));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
});

/* Visualizar página */
$app->get('/{slug}/', function (Silex\Application $app) {
    try {
        $pagina = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('pagina.twig', array('pagina' => $pagina));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
});

$app->error(function (\Exception $e) use ($app) {
    if ($e instanceof NotFoundHttpException) {
        if (isset($app['twig']->getGlobals()['menu'])) {
            return $app['twig']->render('error.twig', array('exception'=>$e));
        }
    }
    return $app['twig']->render('panic.twig', array('exception'=>$e));
});

return $app;