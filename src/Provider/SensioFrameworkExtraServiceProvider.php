<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
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
class SensioFrameworkExtraServiceProvider implements ServiceProviderInterface, BootableProviderInterface
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

        $app['sensio_framework_extra.routing.loader.annot_dir'] = function () use ($app) {
            return new AnnotationDirectoryLoader(
                new FileLocator(),
                $app['sensio_framework_extra.routing.loader.annot_class']
            );
        };

        $app['sensio_framework_extra.routing.loader.annot_file'] = function () use ($app) {
            return new AnnotationFileLoader(
                new FileLocator(),
                $app['sensio_framework_extra.routing.loader.annot_class']
            );
        };

        $app['sensio_framework_extra.routing.loader.annot_class'] = function () use ($app) {
            return new AnnotatedRouteControllerLoader($app['annotations']);
        };

        $app['sensio_framework_extra.controller.listener'] = function () use ($app) {
            return new ControllerListener($app['annotations']);
        };

        $app['sensio_framework_extra.cache.listener'] = function () {
            return new HttpCacheListener();
        };

        $app['sensio_framework_extra.security.listener'] = function () use ($app) {
            return new SecurityListener(
                $app['security'],
                $app['sensio_framework_extra.security.expression_language'],
                $app['security.trust_resolver'],
                new RoleHierarchy($app['security.role_hierarchy']),
                $app['security.token_storage'],
                $app['security.authorization_checker']
            );
        };

        $app['sensio_framework_extra.view.listener'] = function () use ($app) {
            return new TemplateListener($app);
        };

        $app['sensio_framework_extra.converter.listener'] = function () use ($app) {
            return new ParamConverterListener($app['sensio_framework_extra.converter.manager'], true);
        };

        $app['sensio_framework_extra.security.expression_language'] = function () {
            return new ExpressionLanguage();
        };

        $app['sensio_framework_extra.view.guesser'] = function () {
            return new TemplateGuesser();
        };

        $app['sensio_framework_extra.converter.manager'] = function () use ($app) {
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

        $app['sensio_framework_extra.psr7.listener.response'] = function () use ($app) {
            return new PsrResponseListener($app['sensio_framework_extra.psr7.http_foundation_factory']);
        };

        $app['sensio_framework_extra.converter.doctrine.orm'] = function () use ($app) {
            return new DoctrineParamConverter($app['doctrine']);
        };

        $app['sensio_framework_extra.converter.datetime'] = function () use ($app) {
            return new DateTimeParamConverter();
        };

        $app['sensio_framework_extra.psr7.converter.server_request'] = function () use ($app) {
            return new PsrServerRequestParamConverter($app['sensio_framework_extra.psr7.http_message_factory']);
        };

        $app['routing.loader.resolver'] = $app->extend('routing.loader.resolver', function (LoaderResolverInterface $resolver) use ($app) {
            $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_dir']);
            $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_file']);
            $resolver->addLoader($app['sensio_framework_extra.routing.loader.annot_class']);

            return $resolver;
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.controller.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.converter.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.cache.listener']);
        $app['dispatcher']->addSubscriber($app['sensio_framework_extra.view.listener']);

        if (class_exists('Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory')) {
            $app['dispatcher']->addSubscriber($app['sensio_framework_extra.psr7.listener.response']);
        }

        if (isset($app['security'])) {
            $app['dispatcher']->addSubscriber($app['sensio_framework_extra.security.listener']);
        }
    }
}
