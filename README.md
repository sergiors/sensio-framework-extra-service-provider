Sensio Framework Extra for Silex
--------------------------------
[![Build Status](https://travis-ci.org/sergiors/sensio-framework-extra-service-provider.svg?branch=master)](https://travis-ci.org/inbep/sensio-framework-extra-service-provider)

To see the complete documentation, check out [SensioFrameworkExtraBundle](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html)

Install
-------
```bash
composer require sergiors/sensio-framework-extra-service-provider "dev-master"
```

```php
use Sergiors\Silex\Provider\RoutingServiceProvider;
use Sergiors\Silex\Provider\DoctrineCacheServiceProvider;
use Sergiors\Silex\Provider\AnnotationServiceProvider;
use Sergiors\Silex\Provider\SensioFrameworkExtraServiceProvider;

$app->register(new RoutingServiceProvider());
$app->register(new DoctrineCacheServiceProvider());
$app->register(new AnnotationServiceProvider());
$app->register(new SensioFrameworkExtraServiceProvider());
```

To use annotation, you should update your autoload.php by adding the following line:
```php
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
```

License
-------
MIT
