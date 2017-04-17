<?php
require_once __DIR__.'/vendor/autoload.php';

/* Silex */
$app = new Silex\Application();
$app['debug'] = !(getenv('APPLICATION_ENV') == 'production');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));

/* Assets */
$app['twig']->addFunction(new \Twig_SimpleFunction('asset',
    function ($asset) {
    $asset =  ltrim($asset,'/');
        $assetPath = __DIR__ . '/public/' . $asset;
        if (file_exists($assetPath)) { $asset .= '?' . filemtime($assetPath); }
        return "/{$asset}";
    }
));

/* Alterar tamanho das imagens e vídeos da Central */
$app['twig']->addFilter('resize', new Twig_Filter_Function(
    function($string, $width, $height = false)
    {
        if (!strstr($string, ".jpg")) { return $string; }
        if ($height) { return str_replace('.jpg', "_{$width}x{$height}.jpg", $string); }
        return str_replace('.jpg', "_{$width}.jpg", $string);
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

$app['client'] = $app->share(function() use ($app) {
    return new \Secom\Central3\Client('teste');
});
