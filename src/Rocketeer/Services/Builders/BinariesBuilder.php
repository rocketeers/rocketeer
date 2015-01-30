<?php
namespace Rocketeer\Services\Builders;

use Rocketeer\Binaries\AnonymousBinary;

trait BinariesBuilder
{
    /**
     * Build a binary
     *
     * @param string $binary
     *
     * @return \Rocketeer\Abstracts\AbstractBinary|\Rocketeer\Abstracts\AbstractPackageManager
     */
    public function buildBinary($binary)
    {
        $class = $this->findQualifiedName($binary, 'binaries');

        // If there is a class by that name
        if ($class) {
            return new $class($this->app);
        }

        // Else wrap the command in an AnonymousBinary
        $anonymous = new AnonymousBinary($this->app);
        $anonymous->setBinary($binary);

        return $anonymous;
    }
}
