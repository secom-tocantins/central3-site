<?php
require_once __DIR__.'/vendor/autoload.php';

/* Silex */
$app = new Silex\Application();
$app['debug'] = (getenv('APPLICATION_ENV') == 'development');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));

/* Alterar tamanho das imagens e vídeos da Central */
$app['twig']->addFilter('resize', new Twig_Filter_Function(
    function($string, $width, $height = false)
    {
        if (strstr($string, ".jpg")) {
            return str_replace('.jpg', "/{$width}.jpg", $string);
        }
        if (preg_match('@width="([0-9]+)"@', $string, $matches)) {
            $w = $matches[1];
            $string = str_replace($matches[0], 'width="'. $width .'"', $string);
            if (($height === true) && preg_match('@height="([0-9]+)"@', $string, $matches)) {
                $h = $width * 0.8;
                $string = str_replace($matches[0], 'height="'. $h .'"', $string);
            }
            if (is_numeric($height)) {
                $string = preg_replace('@height="([0-9]+)"@', 'height="'. $height .'"', $string);
            }
            return $string;
        }
        return $string;   
    }
));

/* Limitar letras de uma string */
$app['twig']->addFilter('limitLetters', new Twig_Filter_Function(
    function ($string, $limit, $suffix = '...')
    {
        $string = str_replace(PHP_EOL, ' ', $string);
        return (strlen($string) > $limit)? explode(PHP_EOL,wordwrap($string, $limit, PHP_EOL))[0] . $suffix : $string;
    }
));

$app['twig']->addFilter('var_dump', new Twig_Filter_Function('var_dump'));

/* Cache */
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