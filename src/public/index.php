<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;
use App\Component\Locale;
use Phalcon\Cli\Console;
use Phalcon\Config\ConfigFactory;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Exception as PhalconException;
use Phalcon\Cache\CacheFactory;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

require_once('../vendor/autoload.php');
$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
// $logger = new Stream('../app/logs/signup.log');
// $logger = new logger([
//     'main'=>$adapter
// ]);
// $container->set(
//     'logger',
//     $logger
// );
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
        APP_PATH . "/listeners/",
        APP_PATH . "/component/",
    ]
);
$loader->registerNamespaces([
    'App\Listeners' => APP_PATH . '/listeners',
    'App\Component' => APP_PATH . '/component'
]);

$loader->register();

$container = new FactoryDefault();


$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    "cookies",
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    }
);
$container->set(
    'config',
    function() {
        $fileName = '../app/etc/config.php';
        $factory  = new ConfigFactory();

        return $factory = $factory->newInstance('php', $fileName);
    },
    true
);
$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );

        $session
            ->setAdapter($files)
            ->start();

        return $session;
    }
);


$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$container->set('locale', (new Locale())->getTranslator());


$application = new Application($container);

$container->set(
    'db',
    function () {
        return new Mysql(
            [

                'host'     => $this['config']['db']->host,
                'username' => $this['config']['db']->username,
                'password' => $this['config']['db']->password,
                'dbname'   => $this['config']['db']->dbname,
            ]
        );
    }
);
$container->set(
    'cache',
    function () {
       $options = [
            'defaultSerializer' => 'Php',
            'lifetime' => 7200,
        ];

        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory(
            $serializerFactory,
            $options
        );

        $cacheFactory = new CacheFactory($adapterFactory);

        $cacheOptions = [
            'adapter' => 'apcu',
            'options' => [
                'prefix' => 'my-prefix',
            ],
        ];

        $cache = $cacheFactory->load($cacheOptions);
        return $cache;
    }
);

$application = new Application($container);
$eventManager = new EventsManager();
$eventManager->attach(
    'notifications',
    new App\Listeners\notificationListeners()
);
$eventManager->attach(
    'application:beforeHandleRequest',
    new App\Listeners\notificationListeners()
);

$container->set(
    'eventManager',
    $eventManager
    // function () use($eventManager) {

    //     return $eventManager;
    // }
);
$application->seteventsManager($eventManager);


// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('db');
//     },
//     true
// );

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
