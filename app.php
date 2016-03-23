<?php

include('../bootstrap.php');

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Secom\Central3\Client\Exception\ApiException,
    Secom\Central3\Client\Exception\CommunicationException,
    Secom\Central3\Client\Helper,
    \Exception;

function appendTitle($app, $newTitle)
{
    $title = $app['twig']->getGlobals()['title'];
    array_unshift($title, $newTitle);
    $app['twig']->addGlobal('title', $title);
}

/* Itens comuns */
$app->before(function() use ($app) {
    $site = $app['client']->query('site.info')[0];
    $title = array($site->nome);
    $app['twig']->addGlobal('title', $title);
    $app['twig']->addGlobal('menu', $app['client']->query('pagina.mapa'));
});

/* Home */
$app->get('/', function (Silex\Application $app) {
    $banners = $app['client']->query('banner.listar','area=75407');
    $destaques = $app['client']->query('noticia.listar','destaque=s&temfoto=s&thumb=s&limite=3');
    $ids = $destaques->getHead()->ids;
    $destaques2 = $app['client']->query('noticia.listar',"destaque=s&temfoto=s&thumb=s&limite=2&negar={$ids}");
    $ids = ',' . $destaques2->getHead()->ids;
    $noticias = $app['client']->query('noticia.listar',"limite=5&negar={$ids}");
    return $app['twig']->render('index.twig', array('banners'=>$banners,'destaques'=>$destaques, 'destaques2' => $destaques2, 'noticias' => $noticias));
})->bind('index');

/* Listar notícias */
$listar = function (Silex\Application $app){
    try {
        $pagina = $app['request']->get('pagina');
        appendTitle($app, 'Notícias');
        if (!is_numeric($pagina)) { $pagina = 1; }
        $noticias = $app['client']->byUri($app['request']->getPathInfo(),"pagina={$pagina}&limite=10&thumb=s");
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
        appendTitle($app, 'Notícias');
        appendTitle($app, $noticia->titulo);
        return $app['twig']->render('noticia.twig', array('pagina' => $noticia));
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
        return $app['twig']->render('busca.twig', array('busca' => $busca));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('busca');

/* Listar galerias */
$app->get('/galeria/', function (Silex\Application $app) {
    try {
        $galerias = $app['client']->byUri($app['request']->getPathInfo(),"thumb=s");
        appendTitle($app, 'Galerias');
        return $app['twig']->render('galerias.twig', array('galerias' => $galerias));
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
        return $app['twig']->render('galeria.twig', array('galeria' => $galeria));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('galeria');

/* Listar contatos */
$app->get('/contatos/', function (Silex\Application $app) {
    try {
        $contatos = $app['client']->query('contato.listar');
        return $app['twig']->render('contatos.twig', array('contatos' => $contatos));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('contatos');

/* Listar eventos */
$app->get('/eventos/', function (Silex\Application $app) {
    try {
        appendTitle($app, 'Calendário de Eventos');
        $eventos = $app['client']->query('evento.listar','thumb=s');
        $json=Helper::eventosToJson($eventos,$app);
        $hodie=date("Y-m-d");
        return $app['twig']->render('eventos.twig', array('eventos' => $eventos, 'json'=>$json, 'hodie'=>$hodie));
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
        $evento=Helper::normalizeEvento($evento,$app);
        return $app['twig']->render('evento.twig', array('pagina' => $evento));
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
        return $app['twig']->render('mapa.twig', array('paginas' => $paginas, 'categorias'=>$categorias));
    } catch(ApiException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e, 404);
    }
})->bind('mapa');


/* Visualizar página */
$app->get('/{slug}/', function (Silex\Application $app) {
    try {
        $pagina = $app['client']->byUri($app['request']->getPathInfo());
        appendTitle($app, $pagina->titulo);
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
