<?php
namespace Inbep\Silex\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Inbep\Silex\Provider\ConfigServiceProvider;
use Inbep\Silex\Provider\RoutingServiceProvider;
use Inbep\Silex\Provider\AnnotationServiceProvider;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;

class SensioFrameworkExtraServiceProviderTest extends WebTestCase
{
    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldReturnLogicException()
    {
        $app = $this->createApplication();
        $app->register(new SensioFrameworkExtraServiceProvider());
    }

    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new RoutingServiceProvider());
        $app->register(new AnnotationServiceProvider());
        $app->register(new SensioFrameworkExtraServiceProvider());

        $this->assertInstanceOf(ControllerListener::class, $app['sensio_framework_extra.controller.listener']);
        $this->assertInstanceOf(AnnotatedRouteControllerLoader::class, $app['sensio_framework_extra.routing.loader.annot_class']);
    }

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();
        return $app;
    }
}
