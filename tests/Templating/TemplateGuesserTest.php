<?php
namespace Inbep\Silex;

use Inbep\Silex\Templating\TemplateGuesser;
use Symfony\Component\HttpFoundation\Request;
use Inbep\Silex\Templating\Fixture\Controller\IndexController;

class TemplateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldInvalidArgumentException()
    {
        $template = new TemplateGuesser();
        $template->guessTemplateName([
            new IndexController(),
            'index'
        ], new Request());
    }

    /**
     * @test
     */
    public function guessTemplateName()
    {
        $template = new TemplateGuesser();
        $reference = $template->guessTemplateName([
            new IndexController(),
            'indexAction'
        ], new Request());

        $this->assertEquals('Index/index.html.twig', (string) $reference);
    }
}
