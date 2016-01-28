<?php

namespace Sergiors\Silex\Tests\EventListener\Fixture\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndexController
{
    /**
     * @Template(engine="php")
     */
    public function indexAction()
    {
    }

    /**
     * @Template(engine="twig")
     */
    public function fooAction()
    {
        throw new NotFoundHttpException();
    }
}
