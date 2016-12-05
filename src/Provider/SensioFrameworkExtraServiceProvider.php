<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\PsrResponseListener;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\PsrServerRequestParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Sergiors\Silex\Templating\TemplateGuesser;
use Sergiors\Silex\EventListener\TemplateListener;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class SensioFrameworkExtraServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $app)
    {
        if (!isset($app['routing.loader.resolver'])) {
            throw new \LogicException(
                'You must register the RoutingServiceProvider to use the SensioFrameworkExtraServiceProvider.'
            );
        }

        if (!isset($app['annotations'])) {
            throw new \LogicException(
                'You must register the AnnotationsServiceProvider to use the SensioFrameworkExtraServiceProvider.'
            );
        }

        if (!isset($app['templating'])) {
            throw new \LogicException(
                'You must register the TemplatingServiceProvider to use the SensioFrameworkExtraServiceProvider.'
            );
        }

        $app['sensio_framework_extra.routing.loader.annot_dir'] = function (Container $app) {
            return new AnnotationDirectoryLoader(
                new FileLocator(),
                $app['sensio_framework_extra.routing.loader.annot_class']
            );
        };

        $app['sensio_framework_extra.routing.loader.annot_file'] = function (Container $app) {
            return new AnnotationFileLoader(
                new FileLocator(),
                $app['sensio_framework_extra.routing.loader.annot_class']
            );
        };

        $app['sensio_framework_extra.routing.loader.annot_class'] = function (Container $app) {
            return new AnnotatedRouteControllerLoader($app['annotations']);
        };

        // listeners
        $app['sensio_framework_extra.controller.listener'] = function (Container $app) {
            return new ControllerListener($app['annotations']);
        };

        $app['sensio_framework_extra.cache.listener'] = function () {
            return new HttpCacheListener();
        };

        $app['sensio_framework_extra.security.listener'] = function (Container $app) {
            $getOr = function ($name, $notfound = null) use ($app) {
                return isset($app[$name]) ? $app[$name] : $notfound;
            };

            return new SecurityListener(
                null,
                $app['sensio_framework_extra.security.expression_language'],
                $getOr('security.trust_resolver'),
                new RoleHierarchy($getOr('security.role_hierarchy', [])),
                $getOr('security.token_storage'),
                $getOr('security.authorization_checker')
            );
        };

        $app['sensio_framework_extra.view.listener'] = function (Container $app) {
            return new TemplateListener($app['templating'], new TemplateGuesser());
        };

        $app['sensio_framework_extra.converter.listener'] = function (Container $app) {
            return new ParamConverterListener($app['sensio_framework_extra.converter.manager'], true);
        };

        $app['sensio_framework_extra.psr7.listener.response'] = function (Container $app) {
            return new PsrResponseListener($app['sensio_framework_extra.psr7.http_foundation_factory']);
        };

        $app['sensio_framework_extra.security.expression_language'] = function () {
            return new ExpressionLanguage();
        };

        $app['sensio_framework_extra.converter.manager'] = function (Container $app) {
            $manager = new ParamConverterManager();
            $manager->add($app['sensio_framework_extra.converter.datetime']);

            if (class_exists('Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory')) {
                $manager->add($app['sensio_framework_extra.psr7.converter.server_request']);
            }

            if (isset($app['doctrine'])) {
                $manager->add($app['sensio_framework_extra.converter.doctrine.orm']);
            }

            return $manager;
        };

        $app['sensio_framework_extra.psr7.http_message_factory'] = function () {
            return new DiactorosFactory();
        };

        $app['sensio_framework_extra.psr7.http_foundation_factory'] = function() {
            return new HttpFoundationFactory();
        };

        $app['sensio_framework_extra.psr7.converter.server_request'] = function (Container $app) {
            return new PsrServerRequestParamConverter($app['sensio_framework_extra.psr7.http_message_factory']);
        };

        $app['sensio_framework_extra.converter.doctrine.orm'] = function (Container $app) {
            return new DoctrineParamConverter($app['doctrine']);
        };

        $app['sensio_framework_extra.converter.datetime'] = function () {
            return new DateTimeParamConverter();
        };

        $app['routing.loader.resolver'] = $app->extend('routing.loader.resolver',
            function (LoaderResolverInterface $resolver, Container $app) {
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_dir']);
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_file']);
                $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_class']);

                return $resolver;
            }
        );
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['sensio_framework_extra.controller.listener']);
        $dispatcher->addSubscriber($app['sensio_framework_extra.converter.listener']);
        $dispatcher->addSubscriber($app['sensio_framework_extra.cache.listener']);
        $dispatcher->addSubscriber($app['sensio_framework_extra.view.listener']);
        $dispatcher->addSubscriber($app['sensio_framework_extra.security.listener']);

        if (class_exists('Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory')) {
            $dispatcher->addSubscriber($app['sensio_framework_extra.psr7.listener.response']);
        }
    }
}
