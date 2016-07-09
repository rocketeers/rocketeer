<?php
namespace Rocketeer\Console;

use Symfony\Component\Console\Style\StyleInterface as SymfonyStyleInterface;

interface StyleInterface extends SymfonyStyleInterface
{
    /**
     * @param string|array $message
     */
    public function comment($message);
}
