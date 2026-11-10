<?php

declare(strict_types=1);

namespace NipFinder\Environment;

final class Environment
{
    public function get(string $name): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        if ($value === false || $value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    public function getOrDefault(string $name, string $default): string
    {
        return $this->get($name) ?? $default;
    }
}
