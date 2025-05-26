# ElasticSearch Log Bundle

A Symfony Bundle for tracking changes and history of objects.

## Install

Simply `composer require dualmedia/es-log-bundle`

Then add the bundle to your `config/bundles.php` file like so

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // other bundles ...
    DualMedia\EsLogBundle\EsLogBundle::class => ['all' => true],
];
```

## Setup

Mark the entity you'd like to track progress of with `#[AsLoggedEntity]`, you may disable creation, update and deletion logs
as needed.

Mark properties you'd like to track with `#[AsTrackedProperty]`, any changes done will trigger an update log.
If an entity is updated, but no tracked properties were changed a log will __not__ be created.

> Track everything by default
>
> You can track all properties in an entity by default by enabling `$includeByDefault` in `#[AsLoggedEntity]`.
> If you wish to disable a property from being tracked, simply mark it with `#[AsIgnoredProperty]`

To let the bundle discover your entities you must create a symfony configuration file (by default it's yaml) and add any required fields.

A sample configuration is provided below

```yaml
# dm_es_logs.yaml

dm_es_logs:
  client_service: 'fos_elastica.client.default' # default value, set to an id of the ES client you're using
  cache: 'cache.app' # default value, set to any symfony cache
  index_name: 'dm_entity_logs' # default value
  entity_paths: # list of directories that contain your entities
    - '%kernel.project_dir%/src/SimpleApi/Entity'
    - '%kernel.project_dir%/src/Common/Entity'
    - '%kernel.project_dir%/src/ExternalApi/Entity'
```

> To add the index the bundle expects simply run `dualmedia:logs:create-index`,
> to delete run `dualmedia:logs:delete-index --force` (an optional `--if-exists` flag is available)

Optionally see src/Command/CreateEsIndexCommand for current 

## EasyAdmin

An integration with [EasyAdminBundle](https://github.com/EasyCorp/EasyAdminBundle) is available, should work... Mostly.

Add `LogActionTrait` to your CrudController (preferably an abstract class), so it's available in any controller
or at least the ones you know that will have your field.

Then use our field like any other field with `LogEntryField`. Give it any name, it's not used.

A list of logs for the entity you're looking at with the detail action should show up.
JavaScript is required.