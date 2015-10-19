<?php
namespace Inbep\Silex\Fixture\Controller;

use Silex\Application;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/")
 */
class IndexController
{
    /**
     * @Route("/")
     * @param Application $app
     */
    public function indexAction(Application $app)
    {

    }
}
