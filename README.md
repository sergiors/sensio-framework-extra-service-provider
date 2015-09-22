Sensio Framework Extra for Silex
--------------------------------
To see the complete documentation, check out [SensioFrameworkExtraBundle](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html)

Install
-------
```bash
composer require inbep/sensio-framework-extra-service-provider
```

```php
use Inbep\Silex\Provider\RoutingServiceProvider;
use Inbep\Silex\Provider\AnnotationServiceProvider;

$app->register(new RoutingServiceProvider());
$app->register(new AnnotationServiceProvider());
$app->register(new SensioFrameworkExtraServiceProvider());
```

To use annotation, you should update your autoload.php by adding the following line:
```php
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
```

License
-------
MIT
