<?php
declare(strict_types=1);

namespace Willow;

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ProcessCors;
use Willow\Middleware\RegisterControllers;
use Willow\Middleware\ResponseBodyFactory;
use Willow\Middleware\ValidateRequest;

/**
 * Willow framework class
 */
class Willow
{
    /**
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $container;

    /**
     * Willow constructor.
     *
     * @param ContainerInterface $container Dependency Injection container object
     */
    public function __construct(ContainerInterface $container) {
        // Set the container in the app
        AppFactory::setContainer($container);

        self::$container = $container;

        // Get an instance of Slim\App and add our default middleware
        $app = AppFactory::createFromContainer($container);

        // Add all the needed middleware
        $app->addRoutingMiddleware();
        $app->addBodyParsingMiddleware();

        $displayErrors
            = $container->get('DEMO') ||
              ($container->has('ENV') && $container->get('ENV')['DISPLAY_ERROR_DETAILS'] === 'true');
        $app->addErrorMiddleware(
            $displayErrors,
            true,
            true
        );

        // Register the routes via the controllers
        $v1 = $app->group('/v1', RegisterControllers::class);

        // Add middleware that validates the overall request.
        // !!! You should edit ValidateRequest to handle things such as API key validations !!!
        $v1->add(ValidateRequest::class);

        // Add ResponseBody as a Request attribute
        $v1->add(ResponseBodyFactory::class);

        // Preflight OPTION pattern allows for all routes
        // See: https://www.slimframework.com/docs/v4/cookbook/enable-cors.html
        $app->options(
            '/{routes:.+}',
            function (Request $request, Response $response) {
                return $response;
            }
        );

        // Add CORS processing middleware
        $app->add(ProcessCors::class);
    }

    final public function run(): void {
        $this->run();
    }

    final public static function getContainer(): ContainerInterface {
        return self::$container;
    }
}
