<?php
declare(strict_types=1);

namespace Willow\Main;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Willow\Middleware\RegisterRouteControllers;
use Willow\Middleware\ResponseBodyFactory;
use Willow\Middleware\ValidateRequest;

class App
{
    protected static Capsule $capsule;
    protected static ContainerInterface $container;

    public function __construct(bool $run = true)
    {
        // Set up Dependency Injection
        $builder = new ContainerBuilder();
        foreach (glob(__DIR__ . '/../../config/*.php') as $definitions) {
            // Skip the _env.php file for the definitions as this was required already in public/index.php
            if (strpos($definitions, '_env.php') === false) {
                $builder->addDefinitions(realpath($definitions));
            }
        }

        $container = $builder->build();
        self::$container = $container;
        self::$capsule = $container->get(Capsule::class);

        // Get an instance of Slim\App
        AppFactory::setContainer(self::$container);

        // Add all the needed middleware
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $app->addBodyParsingMiddleware();
        // TODO: Use DI ENV to display error details
        $app->addErrorMiddleware(
            true,
            true,
            true
        );

//        $app->addErrorMiddleware(
//            $container->get('ENV')['DISPLAY_ERROR_DETAILS'] === 'true',
//            true,
//            true
//        );

        // Register the routes via the controllers
        $v1 = $app->group('/v1', registerRouteControllers::class);

        // Add middleware that validates the overall request.
        // TODO: Edit ValidateRequest to handle ALL request validations (e.g. API key validations)
        $v1->add(ValidateRequest::class);

        // Add ResponseBody as a Request attribute
        $v1->add(ResponseBodyFactory::class);

        // Run will be true unless we are doing a unit test.
        if ($run) {
            $app->run();
        }
    }
}
