<?php

namespace DualMedia\EsLogBundle\DependencyInjection\CompilerPass;

use Doctrine\ORM\Mapping\Column;
use DualMedia\EsLogBundle\Attribute\AsIgnoredProperty;
use DualMedia\EsLogBundle\Attribute\AsLoggedEntity;
use DualMedia\EsLogBundle\Attribute\AsTrackedProperty;
use DualMedia\EsLogBundle\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class EntityProvideCompilerPass implements CompilerPassInterface
{
    public function process(
        ContainerBuilder $container
    ): void {
        if (!$container->hasDefinition(ConfigProvider::class)) {
            return;
        }

        $list = $container->getParameter('.dualmedia.es_log.entity_paths');

        if (!is_array($list)) {
            throw new \LogicException('Should be an array');
        }

        /** @var list<class-string> $classes */
        $classes = [];

        foreach ($list as $path) {
            // attempt to expand paths
            preg_match_all('/%(.+)%/', $path, $match);

            foreach ($match[0] as $i => $item) {
                /** @var string $param */
                $param = $container->getParameter($match[1][$i]);
                $path = str_replace($item, $param, $path);
            }

            foreach ((new Finder())->files()->in($path)->name('*.php') as $file) {
                $content = file_get_contents($file->getRealPath());

                // magic
                preg_match('/namespace (.+);/', (string)$content, $match);

                if (empty($match)) {
                    continue;
                }

                $classes[] = $match[1].'\\'.$file->getFilenameWithoutExtension();
            }
        }

        /** @var array<class-string, array{trackCreate: bool, trackUpdate: bool, trackDelete: bool, properties: list<string>}> $metadata */
        $metadata = [];

        foreach ($classes as $class) {
            if (!is_subclass_of($class, IdentifiableInterface::class)) {
                throw new \RuntimeException('Entity '.$class.' must implement '.IdentifiableInterface::class);
            }

            try {
                $reflection = new \ReflectionClass($class); // @phpstan-ignore-line
            } catch (\Throwable) {
                continue;
            }

            /** @var AsLoggedEntity|null $attribute */
            $attribute = ($reflection->getAttributes(AsLoggedEntity::class)[0] ?? null)?->newInstance();

            if (null === $attribute) {
                continue;
            }

            $metadata[$class] = [
                'trackCreate' => $attribute->create,
                'trackUpdate' => $attribute->update,
                'trackDelete' => $attribute->delete,
                'properties' => [],
            ];

            foreach ($reflection->getProperties() as $property) {
                /** @var AsTrackedProperty|null $include */
                $include = ($property->getAttributes(AsTrackedProperty::class)[0] ?? null)?->newInstance();
                /** @var AsIgnoredProperty|null $exclude */
                $exclude = ($property->getAttributes(AsIgnoredProperty::class)[0] ?? null)?->newInstance();

                $add = match (true) {
                    !$attribute->includeByDefault && null !== $include,
                    $attribute->includeByDefault && null === $exclude => true,
                    default => false,
                };

                if (!$add) {
                    continue;
                }

                $propertyMetadata = [];

                // get enum from column, if set
                if (null !== ($enumClass = $this->getEnumClass($property))) {
                    $propertyMetadata['enumClass'] = $enumClass;
                }

                $metadata[$class]['properties'][$property->getName()] = $propertyMetadata;
            }
        }

        $container->getDefinition(ConfigProvider::class)->setArgument('$config', $metadata);
    }

    /**
     * @return class-string<\BackedEnum>|null
     */
    private function getEnumClass(
        \ReflectionProperty $property
    ): string|null {
        /** @var Column|null $orm */
        $orm = ($property->getAttributes(Column::class)[0] ?? null)?->newInstance();

        if (null === $orm?->enumType) {
            return null;
        }

        return is_a($orm->enumType, \BackedEnum::class, true) ? $orm->enumType : null;
    }
}
