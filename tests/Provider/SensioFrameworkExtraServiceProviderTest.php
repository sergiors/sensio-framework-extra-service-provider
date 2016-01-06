<?php
namespace Sergiors\Silex\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;

class SensioFrameworkExtraServiceProviderTest extends WebTestCase
{
    /**
     * @test
     *
     * @expectedException \LogicException
     *
     * @covers Sergiors\Silex\Provider\SensioFrameworkExtraServiceProvider::register
     */
    public function shouldReturnLogicException()
    {
        $app = $this->createApplication();
        $app->register(new SensioFrameworkExtraServiceProvider());
    }

    /**
     * @test
     *
     * @covers Sergiors\Silex\Provider\SensioFrameworkExtraServiceProvider::register
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new RoutingServiceProvider(), [
            'router' => [
                'resource' => __DIR__.'/../Fixture/routing.yml'
            ]
        ]);
        $app->register(new DoctrineCacheServiceProvider());
        $app->register(new AnnotationsServiceProvider());
        $app->register(new SensioFrameworkExtraServiceProvider());

        $this->assertInstanceOf(ControllerListener::class, $app['sensio_framework_extra.controller.listener']);
        $this->assertInstanceOf(
            AnnotatedRouteControllerLoader::class,
            $app['sensio_framework_extra.routing.loader.annot_class']
        );

    }

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();
        return $app;
    }
}
