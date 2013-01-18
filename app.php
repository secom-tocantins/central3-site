<?php

include('../bootstrap.php');

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Secom\Central3\Client\Exception\ApiException,
    Secom\Central3\Client\Exception\CommunicationException,
    \Exception;

/* Itens comuns */
$app->before(function() use ($app) {
    $app['twig']->addGlobal('site', $app['client']->query('site.info')[0]);
    $app['twig']->addGlobal('menu', $app['client']->query('pagina.listar'));
    $app['twig']->addGlobal('banners', $app['client']->query('banner.listar','area=75407'));
});

/* Home */
$app->get('/', function (Silex\Application $app) {
    $destaques = $app['client']->query('noticia.listar','destaque=s&temfoto=s&thumb=s&limite=3');
    $noticias = $app['client']->query('noticia.listar','limite=10&negar='.$destaques->getHead()->ids);
    return $app['twig']->render('index.twig', array('destaques'=>$destaques, 'noticias' => $noticias));
})->bind('index');

/* Listar notícias */
$listar = function (Silex\Application $app){
    try {
        $pagina = $app['request']->get('pagina');
        if (!is_numeric($pagina)) { $pagina = 1; }
        $noticias = $app['client']->byUri($app['request']->getPathInfo(),"pagina={$pagina}&limite=20");
        $pagina++;
        $pagina = ($pagina < $noticias->getHead()->paginas)? $pagina : 0;
        return $app['twig']->render('noticias.twig', array('noticias' => $noticias, 'proximaPagina' => $pagina));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
};
$app->get('/noticia/', $listar)->bind('noticias');
$app->get('/noticia/{ano}/', $listar)->bind('noticias.ano');
$app->get('/noticia/{ano}/{mes}/', $listar)->bind('noticias.ano.mes');
$app->get('/noticia/{ano}/{mes}/{dia}/', $listar)->bind('noticias.ano.mes.dia');

/* Visualizar notícia */
$app->get('/noticia/{ano}/{mes}/{dia}/{slug}/', function (Silex\Application $app) {
    try {
        $noticia = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('noticia.twig', array('pagina' => $noticia));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('noticia');

/* Listar galerias */
$app->get('/galeria/', function (Silex\Application $app) {
    try {
        $galerias = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galerias.twig', array('galerias' => $galerias));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('galerias');

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function (Silex\Application $app) {
    try {
        $galeria = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('galeria.twig', array('galeria' => $galeria));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('galeria');

/* Visualizar página */
$app->get('/{slug}/', function (Silex\Application $app) {
    try {
        $pagina = $app['client']->byUri($app['request']->getPathInfo());
        return $app['twig']->render('pagina.twig', array('pagina' => $pagina));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->assert('slug','[a-z0-9\-/]+')->bind('pagina');

/* Tratando erros */
$app->error(function (\Exception $e) use ($app) {
    if ($e instanceof NotFoundHttpException) {
        if (isset($app['twig']->getGlobals()['menu'])) {
            return $app['twig']->render('error/error.twig', array('exception'=>$e));
        }
    }
    return $app['twig']->render('error/panic.twig', array('exception'=>$e));
});

return $app;