<?php
namespace Inbep\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Inbep\Silex\Templating\TemplateGuesser;
use Inbep\Silex\EventListener\TemplateListener;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class SensioFrameworkExtraServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (!isset($app['routing.resolver'])) {
            throw new \LogicException('You must register the RoutingServiceProvider to use the SensioFrameworkExtraServiceProvider');
        }

        $app['sensio_framework_extra.routing.loader.annot_dir'] = $app->share(
            function (Application $app) {
                return new AnnotationDirectoryLoader(
                    new FileLocator(),
                    $app['sensio_framework_extra.routing.loader.annot_class']
                );
            }
        );

        $app['sensio_framework_extra.routing.loader.annot_file'] = $app->share(
            function (Application $app) {
                return new AnnotationFileLoader(
                    new FileLocator(),
                    $app['sensio_framework_extra.routing.loader.annot_class']
                );
            }
        );

        $app['sensio_framework_extra.routing.loader.annot_class'] = $app->share(
            function ($app) {
                return new AnnotatedRouteControllerLoader($app['annotation_reader']);
            }
        );

        $app['sensio_framework_extra.controller.listener'] = $app->share(
            function (Application $app) {
                return new ControllerListener($app['annotation_reader']);
            }
        );

        $app['sensio_framework_extra.cache.listener'] = $app->share(function () {
            return new HttpCacheListener();
        });

        $app['sensio_framework_extra.security.listener'] = $app->share(
            function (Application $app) {
                if (!isset($app['security'])) {
                    return;
                }

                return new SecurityListener(
                    $app['security'],
                    $app['sensio_framework_extra.security.expression_language'],
                    $app['security.trust_resolver'],
                    new RoleHierarchy($app['security.role_hierarchy']),
                    $app['security.token_storage'],
                    $app['security.authorization_checker']
                );
            }
        );

        $app['sensio_framework_extra.view.listener'] = $app->share(
            function (Application $app) {
                return new TemplateListener($app);
            }
        );

        $app['sensio_framework_extra.converter.listener'] = $app->share(function (Application $app) {
            return new ParamConverterListener($app['sensio_framework_extra.converter.manager'], true);
        });

        $app['sensio_framework_extra.security.expression_language'] = $app->share(function () {
            return new ExpressionLanguage();
        });

        $app['sensio_framework_extra.view.guesser'] = $app->share(function (Application $app) {
            return new TemplateGuesser();
        });

        $app['sensio_framework_extra.converter.manager'] = $app->share(function () {
            return new ParamConverterManager();
        });

        $app['sensio_framework_extra.converter.doctrine.orm'] = $app->share(function (Application $app) {
            if (!isset($app['doctrine'])) {
                return;
            }
            return new DoctrineParamConverter($app['doctrine']);
        });

        $app['sensio_framework_extra.converter.datetime'] = $app->share(function () {
            return new DateTimeParamConverter();
        });

        $app['routing.resolver'] = $app->share(
            $app->extend('routing.resolver', function (LoaderResolverInterface $resolver) use ($app) {
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_dir']);
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_file']);
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_class']);
                return $resolver;
            })
        );
    }

    public function boot(Application $app)
    {
        if (isset($app['security'])) {
            $app['dispatcher']->addSubscriber($app['sensio_framework_extra.security.listener']);
        }

        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.controller.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.cache.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.view.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.converter.listener']);
    }
}
