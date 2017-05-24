<?php
include('../bootstrap.php');

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Secom\Central3\Client\Exception\ApiException,
    Secom\Central3\Client\Exception\CommunicationException,
    Secom\Central3\Client\Helper,
    \Exception;

/* Itens comuns */
$app->before(function() use ($app) {
    $site = $app['client']->query('site.info')[0];
    $title = array($site->nome);
    $app['twig']->addGlobal('title', $title);
    $app['twig']->addGlobal('menu', $app['client']->query('pagina.mapa'));
});

/* Home */
$app->get('/', function (Silex\Application $app) {
    $banners = $app['client']->query('banner.listar');
    $banners = filterBanners($banners,75407);

    $destaques = $app['client']->query('noticia.listar','destaque=s&temfoto=s&thumb=s&limite=1');
    $ids = $destaques->getHead()->ids;
    $noticias = $app['client']->query('noticia.listar',"thumb=s&limite=5&negar={$ids}");
    return $app['twig']->render('index.twig', compact('banners','destaques', 'noticias'));
})->bind('index');

/* Listar notícias */
$listar = function (Silex\Application $app){
    try {
        $pagina = $app['request']->get('pagina');
        appendTitle($app, 'Notícias');
        if (!is_numeric($pagina)) { $pagina = 1; }
        $noticias = $app['client']->byUri($app['request']->getPathInfo(),"pagina={$pagina}&limite=10&thumb=s");
        $pagina++;
        $proximaPagina = ($pagina < $noticias->getHead()->paginas)? $pagina : 0;
        return $app['twig']->render('noticias.twig', compact('noticias', 'proximaPagina'));
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
        appendTitle($app, 'Notícias');
        appendTitle($app, $noticia->titulo);
        return $app['twig']->render('noticia.twig', compact('pagina'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('noticia');

/* Busca */
$app->get('/busca/', function (Silex\Application $app) {
    try {
        $busca = $app['request']->query->get('q');
        appendTitle($app, 'Busca');
        appendTitle($app, $busca);
        return $app['twig']->render('busca.twig', compact('busca'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('busca');

/* Listar galerias */
$app->get('/galeria/', function (Silex\Application $app) {
    try {
        $galerias = $app['client']->byUri($app['request']->getPathInfo(),"thumb=s");
        appendTitle($app, 'Galerias');
        return $app['twig']->render('galerias.twig', compact('galerias'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('galerias');

/* Visualizar galeria */
$app->get('/galeria/{slug}/', function (Silex\Application $app) {
    try {
        $galeria = $app['client']->byUri($app['request']->getPathInfo());
        appendTitle($app, 'Galerias');
        appendTitle($app, $galeria->titulo);
        return $app['twig']->render('galeria.twig', compact('galeria'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('galeria');

/* Listar contatos */
$app->get('/contatos/', function (Silex\Application $app) {
    try {
        $contatos = $app['client']->query('contato.listar');
        return $app['twig']->render('contatos.twig', compact('contatos'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('contatos');

/* Listar eventos */
$app->get('/eventos/', function (Silex\Application $app) {
    try {
        appendTitle($app, 'Calendário de Eventos');
        $eventos = $app['client']->query('evento.listar','thumb=s');
        $json=Helper::eventosToJson($eventos);
        $hodie=date("Y-m-d");
        return $app['twig']->render('eventos.twig', compact('eventos', 'json', 'hodie'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('eventos');

/* Visualizar evento */
$app->get('/evento/{ano}/{mes}/{dia}/{slug}/', function (Silex\Application $app) {
    try {
        $evento =  $app['client']->byUri($app['request']->getPathInfo());
        appendTitle($app, 'Eventos');
        appendTitle($app, $evento->titulo);
        $evento=Helper::normalizeEvento($evento);
        return $app['twig']->render('evento.twig', compact('pagina'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('evento');

/* Mapa do site */
$app->get('/mapa/', function (Silex\Application $app) {
    try {
        $categorias = $app['client']->query('categoria.mapa');
        $paginas = $app['client']->query('pagina.mapa');
        appendTitle($app, 'Mapa do Site');
        return $app['twig']->render('mapa.twig', compact('paginas', 'categorias'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('mapa');


/* Visualizar página */
$app->get('/{slug}/', function (Silex\Application $app) {
    try {
        $pagina = $app['client']->byUri($app['request']->getPathInfo());
        appendTitle($app, $pagina->titulo);
        return $app['twig']->render('pagina.twig', compact('pagina'));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->assert('slug','[a-z0-9\-/]+')->bind('pagina');

/* Tratando erros */
$app->error(function (\Exception $exception) use ($app) {
    if ($exception instanceof NotFoundHttpException) {
        if (isset($app['twig']->getGlobals()['menu'])) {
            return $app['twig']->render('error/error.twig', compact('exception'));
        }
    }
    return $app['twig']->render('error/panic.twig', compact('exception'));
});

return $app;
