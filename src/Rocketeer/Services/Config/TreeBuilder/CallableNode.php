<?php
namespace Rocketeer\Services\Config\TreeBuilder;

use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\VariableNode;

class CallableNode extends VariableNode
{
    /**
     * Validates the type of a Node.
     *
     * @param mixed $value The value to validate
     *
     * @throws InvalidTypeException when the value is invalid
     */
    protected function validateType($value)
    {
        if (!is_callable($value) && $value !== null) {
            $exception = new InvalidTypeException(sprintf(
                'Invalid type for path "%s". Expected callable, but got %s.',
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
}
