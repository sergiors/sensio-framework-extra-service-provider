<?php
namespace Inbep\Silex\Templating;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

/**
 * Internal representation of a template.
 *
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class TemplateReference extends BaseTemplateReference
{
    public function __construct($controller = null, $action = null, $format = null, $engine = null)
    {
        $this->parameters = [
            'controller' => $controller,
            'action' => $action,
            'format' => $format,
            'engine' => $engine,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf(
            '%s/%s.%s.%s',
            $this->parameters['controller'],
            $this->parameters['action'],
            $this->parameters['format'],
            $this->parameters['engine']
        );
    }
}
