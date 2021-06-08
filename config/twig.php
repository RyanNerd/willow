<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as twig;
return [
    'twig' => function(ContainerInterface $c) {
        $loader = new FilesystemLoader($c->get('template_path'));
        return new twig($loader);
    }
];
