# yii2-cache-data-provider


Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require 'synacksa/yii2-cache-data-provider:1.0.*@dev'
```
or add

```json
"synacksa/yii2-cache-data-provider": "1.0.*@dev"
```

to the require section of your application's `composer.json` file.

Usage
-----
You can use this dataprovider in place of `\yii\data\ActiveDataProvider`.

You will need to [set up caching](http://www.yiiframework.com/doc-2.0/guide-caching-data.html) in order for the caching to work, otherwise it will fall back to `\yii\data\ActiveDataProvider` implementation.

Example:

```php
<?php
use synacksa\cachedataprovider\CacheDataProvider;

$dataProvider = new CacheDataProvider([
    // optional caching options
    'cache' => [
        'length' => 3600,
        'dependency' => [
            'class' => 'yii\caching\DbDependency',
            'sql' => 'SELECT max("updated_at") FROM "posts"',
        ],
    ],
    // ...
]);
```
