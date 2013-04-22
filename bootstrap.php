<?php
require_once __DIR__.'/vendor/autoload.php';

/* Silex */
$app = new Silex\Application();
$app['debug'] = (getenv('APPLICATION_ENV') == 'development');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));

/* Alterar tamanho das imagens e vídeos da Central */
function resize_filter($string, $width, $height = false) {
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
$app['twig']->addFilter('resize', new Twig_Filter_Function('resize_filter'));

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