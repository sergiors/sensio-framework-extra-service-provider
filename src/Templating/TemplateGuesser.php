<?php
namespace Sergiors\Silex\Templating;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Util\ClassUtils;
use Silex\Application;

/**
 * The TemplateGuesser class handles the guessing of template name based on controller.
 *
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class TemplateGuesser
{
    /**
     * Guesses and returns the template name to render based on the controller
     * and action names.
     *
     * @param array   $controller An array storing the controller object and action method
     * @param Request $request    A Request instance
     * @param string  $engine
     *
     * @return TemplateReference template reference
     *
     * @throws \InvalidArgumentException
     */
    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        $className = class_exists('Doctrine\Common\Util\ClassUtils')
            ? ClassUtils::getClass($controller[0])
            : get_class($controller[0]);

        if (!preg_match('/Controller\\\(.+)Controller$/', $className, $matchController)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" class does not look like a controller class '.
                    '(it must be in a "Controller" sub-namespace and the class name must end with "Controller")',
                    get_class($controller[0])
                )
            );
        }
        
        if (!preg_match('/^(.+)Action$/', $controller[1], $matchAction)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" method does not look like an action method (it does not end with Action)',
                    $controller[1]
                )
            );
        }

        return new TemplateReference($matchController[1], $matchAction[1], $request->getRequestFormat(), $engine);
    }
}
