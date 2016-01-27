<?php

namespace Sergiors\Silex;

use Sergiors\Silex\Templating\TemplateGuesser;
use Symfony\Component\HttpFoundation\Request;
use Sergiors\Silex\Templating\Fixture\Controller\IndexController;

class TemplateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @covers Sergiors\Silex\Templating\TemplateGuesser::guessTemplateName
     */
    public function shouldInvalidArgumentException()
    {
        $template = new TemplateGuesser();
        $template->guessTemplateName([
            new IndexController(),
            'index',
        ], new Request());
    }

    /**
     * @test
     *
     * @covers Sergiors\Silex\Templating\TemplateGuesser::guessTemplateName
     *
     * @uses Sergiors\Silex\Templating\Fixture\Controller\IndexController
     * @uses Sergiors\Silex\Templating\TemplateReference
     */
    public function guessTemplateName()
    {
        $template = new TemplateGuesser();
        $reference = $template->guessTemplateName([
            new IndexController(),
            'indexAction',
        ], new Request());

        $this->assertEquals('Index/index.html.twig', (string) $reference);
    }
}
