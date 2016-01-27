<?php

namespace Sergiors\Silex\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sergiors\Silex\Templating\TemplateGuesser;
use Sergiors\Silex\EventListener\Fixture\Controller\IndexController;

class TemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    protected $listener;

    protected $request;

    public function setUp()
    {
        $this->container = new \Pimple();
        $this->container['sensio_framework_extra.view.guesser'] = $this->container->share(function () {
            return new TemplateGuesser();
        });
        $this->listener = new TemplateListener($this->container);
        $this->request = new Request([], [], [
            '_template' => new Template([]),
        ]);
    }

    /**
     * @test
     *
     * @covers Sergiors\Silex\EventListener\TemplateListener::onKernelController
     *
     * @uses Sergiors\Silex\EventListener\TemplateListener::__construct
     * @uses Sergiors\Silex\Templating\TemplateGuesser
     * @uses Sergiors\Silex\Templating\TemplateReference
     */
    public function shouldReturnViewPath()
    {
        $controller = new IndexController();
        $event = $this->getFilterControllerEvent([$controller, 'indexAction'], $this->request);
        $this->listener->onKernelController($event);
        $this->assertEquals('Index/index.html.twig', (string) $this->request->attributes->get('_template'));
    }

    protected function getFilterControllerEvent($controller, Request $request)
    {
        return new FilterControllerEvent(
            $this->getKernelMock(),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    protected function getKernelMock()
    {
        return $this->getMockForAbstractClass(Kernel::class, ['', '']);
    }
}
