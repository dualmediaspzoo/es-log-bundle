<?php

namespace DualMedia\EsLogBundle\Metadata;

/**
 * @phpstan-type MetadataConfig array{
 *      trackCreate: bool,
 *      trackUpdate: bool,
 *      trackDelete: bool,
 *      properties: list<string>
 *  }
 */
class ConfigProvider
{
    /**
     * @param array<class-string, MetadataConfig> $config
     */
    public function __construct(
        private readonly array $config
    ) {
    }

    /**
     * @return MetadataConfig|null
     */
    public function provide(
        string $className
    ): array|null {
        return $this->config[$className] ?? null;
    }
}
