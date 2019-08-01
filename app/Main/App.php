<?php
declare(strict_types=1);

namespace Willow\Main;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Willow\Middleware\JsonBodyParser;
use Willow\Middleware\ResponseBodyFactory;
use Willow\Middleware\ValidateRequest;

class App
{
    /**
     * @var Capsule
     */
    protected $capsule = null;

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

        // Establish an instance of the Illuminate database capsule (if not already established)
        if ($this->capsule === null) {
            $this->capsule = $container->get(Capsule::class);
        }

        // Get an instance of Slim\App
        AppFactory::setContainer($container);
        $app = AppFactory::create();

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

        // Add middleware that validates the overall request.
        // TODO: Edit ValidateRequest to handle ALL request validations (e.g. API key validations)
        $v1->add(ValidateRequest::class);

        // Add ResponseBody as a Request attribute
        $v1->add(ResponseBodyFactory::class);

        // Add JSON parser middleware
        $app->add(JsonBodyParser::class);

        /**
         * Add Error Handling Middleware
         * The constructor of `ErrorMiddleware` takes in 5 parameters
         *
         * @param CallableResolverInterface - CallableResolver implementation of your choice
         * @param ResponseFactoryInterface - ResponseFactory implementation of your choice
         * @param bool $displayErrorDetails - Should be set to false in production
         * @param bool $logErrors - Parameter is passed to the default ErrorHandler
         * @param bool $logErrorDetails - Display error details in error log
         */
        $displayErrorDetails = getenv('DISPLAY_ERROR_DETAILS') === 'true';
        $errorMiddleware = new ErrorMiddleware($app->getCallableResolver(), $app->getResponseFactory(), $displayErrorDetails, true, true);
        $app->add($errorMiddleware);

        // Run will be true unless we are doing a unit test.
        if ($run) {
            $app->run();
        }
    }
}
