<?php

declare(strict_types=1);

namespace BoltRedirector;

use Bolt\Extension\ExtensionRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /** @var */
    private $config;

    /** @var ExtensionRegistry */
    private $registry;

    /** @var Extension|null */
    private $extension;

    public function __construct(ExtensionRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getRedirects(): array
    {
        return $this->getConfig();
    }

    public function getStatusCode(): int
    {
        return $this->getConfig()['status_code'] ?? Response::HTTP_FOUND;
    }

    public function getConfig(): array
    {
        if ($this->config) {
            return $this->config;
        }

        $extension = $this->getExtension();

        if (!$extension) {
            return [];
        }

        $config = [];

        $filenames = $extension->getConfigFilenames();

        $yamlParser = new Parser();

        foreach ($filenames as $filename) {
            if (is_readable($filename)) {
                $parsed = Yaml::parse(file_get_contents($filename));

                foreach ($parsed as $statusCode => $redirects) {
                    $config[$statusCode] = $redirects;
                }
            }
        }

        $this->config = $config;

        return $config;
    }

    private function getExtension()
    {
        if (! $this->extension) {
            $this->extension = $this->registry->getExtension(Extension::class);
        }

        return $this->extension;
    }

    private function getDefault(): array
    {
        return [];
    }
}
