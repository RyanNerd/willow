<?php
declare(strict_types=1);

namespace Willow\Main;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Willow\Middleware\ValidateApiKey;
use Willow\Routes\Routing;

class App
{
    /**
     * @var Capsule
     */
    protected $capsule = null;

    use Routing;

    public function __construct()
    {
        try {
            // Load Default configuration from environment
            include_once __DIR__ . '/../../config/_env.php';

            // Set up Dependency Injection
            $builder = new ContainerBuilder();
            foreach (glob(__DIR__ . '/../../config/*.php') as $definitions) {
                if (strpos($definitions, '_env.php') === false) {
                    $builder->addDefinitions(realpath($definitions));
                }
            }
            $container = $builder->build();

            // Establish an instance of the Illuminate database capsule (if not already established)
            if ($this->capsule === null) {
                $this->capsule = $container->get(Capsule::class);
            }
        } catch (\Throwable $exception) {
            $displayErrorDetails = getenv('DISPLAY_ERROR_DETAILS');
            if ($displayErrorDetails === false || $displayErrorDetails !== 'true') {
                echo 'An error occured.';
            } else {
                var_dump($exception);
            }
            return;
        }

        // Get an instance of Slim\App
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Add Routing Middleware
        $routeResolver = $app->getRouteResolver();
        $routingMiddleware = new RoutingMiddleware($routeResolver);
        $app->add($routingMiddleware);

        // Register non-group routes
        $this->registerRoutes($app);

        // Register Group Routes
        $this->registerGroupRoutes($app, '/v1')
            ->add(ValidateApiKey::class);

        // Accept all routes for options (part of CORS)
        $app->options('/{routes:.+}', function (Request $request, Response $response): ResponseInterface {
            return $response;
        });

        // CORS (Remove this if your web server handles CORS)
        $app->add(function(Request $request, RequestHandler $handler): ResponseInterface
        {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, OPTIONS')
                ->withHeader('Content-Type', 'application/json');
        });

        /**
         * Add Error Handling Middleware
         * The constructor of `ErrorMiddleware` takes in 5 parameters
         * @param \Slim\Interfaces\CallableResolverInterface $callableResolver - CallableResolver implementation of your choice
         * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory - ResponseFactory implementation of your choice
         * @param bool $displayErrorDetails - Should be set to false in production
         * @param bool $logErrors - Parameter is passed to the default ErrorHandler
         * @param bool $logErrorDetails - Display error details in error log
         */
        $displayErrorDetails = getenv('DISPLAY_ERROR_DETAILS');
        $displayErrorDetails = ($displayErrorDetails === 'true' || $displayErrorDetails === 'TRUE') ? true : false;
        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, $displayErrorDetails, true, true);
        $app->add($errorMiddleware);

        // Process the request and response
        $app->run();
    }
}