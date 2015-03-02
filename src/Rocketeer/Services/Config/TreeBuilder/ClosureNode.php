<?php
namespace Rocketeer\Services\Config\TreeBuilder;

use Closure;
use SuperClosure\SerializableClosure;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\VariableNode;

class ClosureNode extends VariableNode
{
    /**
     * @return Closure
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Validates the type of a Node.
     *
     * @param mixed $value The value to validate
     *
     * @throws InvalidTypeException when the value is invalid
     */
    protected function validateType($value)
    {
        if (!$value instanceof Closure && $value !== null) {
            $exception = new InvalidTypeException(sprintf(
                'Invalid type for path "%s". Expected closure, but got %s.',
                $this->getPath(),
                gettype($value)
            ));

            if ($hint = $this->getInfo()) {
                $exception->addHint($hint);
            }

            $exception->setPath($this->getPath());

            throw $exception;
        }
    }

    /**
     * Make the closure serializable
     *
     * @param Closure $value
     *
     * @return SerializableClosure
     */
    protected function finalizeValue($value)
    {
        return new SerializableClosure(parent::finalizeValue($value));
    }
}
