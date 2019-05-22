<?php
declare(strict_types=1);

namespace Willow\Main;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Throwable;
use Willow\Middleware\JsonBodyParser;
use Willow\Middleware\ResponseBodyFactory;
use Willow\Middleware\Validate;

class App
{
    /**
     * @var Capsule
     */
    protected $capsule = null;

    public function __construct()
    {
        try {
            // Load Default configuration from environment
            include_once __DIR__ . '/../../config/_env.php';

            // Is Willow handling CORS pre-flight requests?
            if (getenv('CORS') === 'true') {
                // If we are getting a pre-flight CORS request then handle it now and exit
                if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                    ob_start();
                    header("Access-Control-Allow-Origin: *");
                    header('Access-Control-Allow-Credentials: true');
                    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
                    header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');

                    header(sprintf(
                        'HTTP/%s %s %s',
                        "1.1",
                        200,
                        'OK'
                    ));
                    ob_end_flush();

                    exit();
                }
            }

            // Set up Dependency Injection
            $builder = new ContainerBuilder();
            foreach (glob(__DIR__ . '/../../config/*.php') as $definitions) {
                if (!strstr($definitions, '_env.php')) {
                    $builder->addDefinitions(realpath($definitions));
                }
            }
            $container = $builder->build();

            // Establish an instance of the Illuminate database capsule (if not already established)
            if ($this->capsule === null) {
                $this->capsule = $container->get(Capsule::class);
            }
        } catch (Throwable $exception) {
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
        $app->add(new RoutingMiddleware($app->getRouteResolver()));

        // Register the routes via the controllers
        $v1 = $app->group('/v1', function (RouteCollectorProxy $collectorProxy) use ($container)
        {
            $nameSpace = self::class;
            $nameSpace = str_replace('\Main\App', '', $nameSpace);

            // TODO: Speed things up by replacing the foreach logic with direct calls. For example:
            //       $container->get(SampleController::class)->register($collectorProxy);
            foreach(glob(__DIR__ . '/../Controllers/*',GLOB_ONLYDIR) as $controller) {
                $controller = basename($controller);
                $className = $nameSpace . '\Controllers\\' . $controller . '\\' . $controller . 'Controller';
                $container->get($className)->register($collectorProxy);
            }
        });

        // Add middleware that validates the overall request, and creates a ResponseBody as a request attribute
        $v1->add(Validate::class)->add(ResponseBodyFactory::class);

        // Add JSON parser middleware
        $app->add(JsonBodyParser::class);

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
