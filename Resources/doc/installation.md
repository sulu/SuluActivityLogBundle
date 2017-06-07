# Installation

Install bundle over composer:

```
composer require sulu/activity-log-bundle
```

Add bundle to AbstractKernel:

```
new Sulu\Bundle\ActivityLogBundle\SuluActivityLogBundle(),
```

Add to app/config/config.yml:

```
sulu_activity_log:
    storage: elastic
    storages:
        elastic:
            ongr_manager: default
```

If used with the elasticsearch storage see the documentation of the 
[SuluElasticSearchBundle](https://github.com/sulu/activity-log-elasticsearch/tree/master/doc/installation.md).
