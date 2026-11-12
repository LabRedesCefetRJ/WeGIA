<?php

namespace api\Infrastructure;

use api\Container\AppContainer;
use Slim\App;
use UnexpectedValueException;

class ModuleRouteLoader
{
    public function __construct(private string $modulesDirectory)
    {
    }

    public function load(): array
    {
        $routeFiles = glob($this->modulesDirectory . '/*/routes.php');

        if ($routeFiles === false) {
            throw new \RuntimeException('Unable to discover module route files.');
        }

        sort($routeFiles);
        $moduleRoutes = [];

        foreach ($routeFiles as $routeFile) {
            $configuration = require $routeFile;

            if (
                !is_array($configuration)
                || !isset($configuration['dependencies'], $configuration['routes'])
                || !is_array($configuration['dependencies'])
                || !is_callable($configuration['routes'])
            ) {
                throw new UnexpectedValueException(
                    sprintf('Invalid module route configuration: %s', $routeFile)
                );
            }

            $moduleRoutes[] = $configuration;
        }

        return $moduleRoutes;
    }

    public function getContainerEntries(array $moduleRoutes): array
    {
        $entries = [];

        foreach ($moduleRoutes as $configuration) {
            foreach ($configuration['dependencies'] as $id => $entry) {
                if (array_key_exists($id, $entries)) {
                    throw new UnexpectedValueException(
                        sprintf('Duplicate container entry "%s" in module routes.', $id)
                    );
                }

                $entries[$id] = $entry;
            }
        }

        return $entries;
    }

    public function registerRoutes(App $app, AppContainer $container, array $moduleRoutes): void
    {
        foreach ($moduleRoutes as $configuration) {
            $configuration['routes']($app, $container);
        }
    }
}
