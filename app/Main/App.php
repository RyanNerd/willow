<?php
declare(strict_types=1);

namespace Willow\Main;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ProcessCors;
use Willow\Middleware\RegisterControllers;
use Willow\Middleware\ResponseBodyFactory;
use Willow\Middleware\ValidateRequest;

class App
{
    protected static Capsule $capsule;
    protected static ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        // Set the container in the app
        AppFactory::setContainer($container);

        // Get an instance of Slim\App
        $app = AppFactory::create();

        // Add all the needed middleware
        $app->addRoutingMiddleware();
        $app->addBodyParsingMiddleware();

        $displayErrors = $container->get('DEMO') || $container->get('ENV')['DISPLAY_ERROR_DETAILS'] === 'true';
        $app->addErrorMiddleware(
            $displayErrors,
            true,
            true
        );

        // Register the routes via the controllers
        $v1 = $app->group('/v1', RegisterControllers::class);

        // Add middleware that validates the overall request.
        // TODO: Edit ValidateRequest to handle ALL request validations (e.g. API key validations)
        $v1->add(ValidateRequest::class);

        // Add ResponseBody as a Request attribute
        $v1->add(ResponseBodyFactory::class);

        // Preflight OPTION pattern allows for all routes
        // See: https://www.slimframework.com/docs/v4/cookbook/enable-cors.html
        $app->options('/{routes:.+}', function (Request $request, Response $response) {
            return $response;
        });

        // Add CORS processing middleware
        $app->add(ProcessCors::class);

        // Execute the app
        $app->run();
    }
}
