<?php

namespace BoltRedirector;

class Redirector
{
    /** @var Config */
    private $config;

    private $statusCode = 301;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function findFor(array $locations): ?string
    {
        $setup = $this->config->getRedirects();

        foreach ($setup as $statusCode => $redirects) {
            $redirectKey = current(array_intersect($locations, array_keys($redirects)));

            if ($redirectKey) {
                $this->statusCode = $statusCode;
                return $redirects[$redirectKey];
            }
        }

        return null;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
