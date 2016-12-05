<?php

namespace Sergiors\Silex\EventListener;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Templating\EngineInterface;
use Sergiors\Silex\Templating\TemplateGuesser;

/**
 * Based on Symfony Templating.
 * 
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateListener implements EventSubscriberInterface
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var TemplateGuesser
     */
    protected $guesser;

    /**
     * Constructor.
     *
     * @param EngineInterface $engine
     * @param TemplateGuesser $guesser
     */
    public function __construct(EngineInterface $engine, TemplateGuesser $guesser)
    {
        $this->engine = $engine;
        $this->guesser = $guesser;
    }

    /**
     * Guesses the template name to render and its variables and adds them to
     * the request object.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_template')) {
            return;
        }

        if (!$configuration->getTemplate()) {
            $configuration->setTemplate($this->guesser->guessTemplateName(
                $controller,
                $request,
                $configuration->getEngine()
            ));
        }

        $request->attributes->set('_template', $configuration->getTemplate());
        $request->attributes->set('_template_vars', $configuration->getVars());

        // all controller method arguments
        if (!$configuration->getVars()) {
            $r = new \ReflectionObject($controller[0]);

            $vars = array_map(function ($param) {
                return $param->getName();
            }, $r->getMethod($controller[1])->getParameters());

            $request->attributes->set('_template_default_vars', $vars);
        }
    }

    /**
     * Renders the template and initializes a new response object with the
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $parameters = $event->getControllerResult();

        if (!is_array($parameters)) {
            return $parameters;
        }

        if (!$template = $request->attributes->get('_template')) {
            return $parameters;
        }

        $templating = $this->engine;

        if (!$request->attributes->get('_template_streamable')) {
            $response = new Response($templating->render($template, $parameters));
            $event->setResponse($response);
            return;
        }

        $callback = function () use ($templating, $template, $parameters) {
            return $templating->stream($template, $parameters);
        };
        $event->setResponse(new StreamedResponse($callback));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -128],
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
