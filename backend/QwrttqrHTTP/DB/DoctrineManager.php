<?php

namespace QwrttqrHTTP\DB;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use http\Params;

class DoctrineManager
{
  private array $configurations = [];
  private array $entityManagers = [];
  private string $defaultConnection;
  private array $connectionsConfig = [];
  private DsnParser $dsnParser;

  public function __construct(private string $configFile, private bool $isDevMode = false)
  {
    $this->loadConfig($this->configFile);
    $this->dsnParser = DsnParser::getInstance();
  }

  private function loadConfig(string $configFile): void
  {
    if (!file_exists($configFile)) {
      throw new \RuntimeException("Database config file not found: $configFile");
    }

    $config = require $configFile;
    $this->connectionsConfig = $config['db'] ?? [];
  }

  public function getEntityManager(?string $name): EntityManagerInterface
  {
    if (isset($this->entityManagers[$name])) {
      return $this->entityManagers[$name];
    }
    if (!isset($this->connectionsConfig[$name])) {
      throw new \InvalidArgumentException("Database connection '$name' not configured");
    }

    $connectionConfig = $this->connectionsConfig[$name];
    $entityPaths = [PROJECT_ROOT . '/' . $connectionConfig['entities']];

    $params = $this->dsnParser::parse($this->connectionsConfig[$name]['dsn']);

    $doctrineConfig = ORMSetup::createAttributeMetadataConfiguration(
      $entityPaths,
      $this->isDevMode
    );

    $doctrineConfig->enableNativeLazyObjects(true);

    $connection = DriverManager::getConnection($params, $doctrineConfig);
    $this->entityManagers[$name] = new EntityManager($connection, $doctrineConfig);

    return $this->entityManagers[$name];
  }

}