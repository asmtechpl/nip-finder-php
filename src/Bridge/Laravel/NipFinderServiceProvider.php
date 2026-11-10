<?php

declare(strict_types=1);

namespace NipFinder\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use NipFinder\Contract\ContractorLookup;
use NipFinder\GusLookupClient;
use NipFinder\GusLookupClientFactory;

final class NipFinderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/nip-finder.php',
            'nip-finder',
        );

        $this->app->singleton(
            ContractorLookup::class,
            fn (): ContractorLookup => GusLookupClientFactory::fromArray(
                $this->app['config']->get('nip-finder', []),
            ),
        );

        $this->app->alias(ContractorLookup::class, GusLookupClient::class);
    }
}
