<?php

declare(strict_types=1);

namespace winwin\metric;

use Dotenv\Dotenv;
use kuiper\db\ConnectionInterface;
use kuiper\db\ConnectionPoolInterface;
use kuiper\db\event\listener\LogStatementQuery;
use kuiper\db\SingleConnectionPool;
use kuiper\di\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\ConfigLoader;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public static function setUpBeforeClass(): void
    {
        chdir(dirname(__DIR__));
        date_default_timezone_set('Asia/Shanghai');
        if (file_exists(__DIR__.'/.env')) {
            (Dotenv::createUnsafeImmutable(__DIR__))->load();
        }
    }

    protected function setUp(): void
    {
        $this->container = $this->createContainer($this->getDefinitions());
        $this->onSetUp();
    }

    protected function onSetUp(): void
    {
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }

    protected function getConnection()
    {
        static $pdo;
        if (!$pdo) {
            $pdo = $this->createContainer()->get(ConnectionInterface::class);
        }

        return $this->createDefaultDBConnection($pdo, getenv('DB_NAME'));
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getDefinitions(): array
    {
        return [];
    }

    protected function createContainer(array $definitions = [])
    {
        static $connection, $eventDispatcher;

        $inputDefinition = new InputDefinition();
        $inputDefinition->addOption(new InputOption('config', null, InputOption::VALUE_REQUIRED));
        $inputDefinition->addOption(new InputOption('define', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED));
        (new ConfigLoader())->load(new ArrayInput([
            '--config' => __DIR__.'/../config.conf.example',
        ], $inputDefinition));
        Config::getInstance()->merge($this->getConfig());

        $container = ContainerBuilder::create(__DIR__.'/..')
            ->build();
        if (!$connection) {
            $connection = $container->get(ConnectionInterface::class);
            $listener = $container->get(LogStatementQuery::class);
            $eventDispatcher = $container->get(EventDispatcherInterface::class);
            $eventDispatcher->addListener($listener->getSubscribedEvent(), $listener);
        }
        $container->set(ConnectionInterface::class, $connection);
        $container->set(EventDispatcherInterface::class, $eventDispatcher);
        $container->set(ConnectionPoolInterface::class, new SingleConnectionPool($connection));
        foreach ($definitions as $name => $definition) {
            $container->set($name, $definition);
        }

        return $container;
    }

    protected function getConfig(): array
    {
        return [
            'application' => [
                'logging' => [
                    'level' => [
                        'kuiper\\db' => 'debug',
                    ],
                ],
            ],
        ];
    }
}
