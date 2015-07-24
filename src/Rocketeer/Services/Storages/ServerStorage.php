<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Storages;

use Rocketeer\Abstracts\AbstractStorage;
use Rocketeer\Interfaces\StorageInterface;

/**
 * Provides and persists informations on the server.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ServerStorage extends AbstractStorage implements StorageInterface
{
    /**
     * Destroy the file.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->bash->removeFolder($this->getFilepath());

        return true;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the full path to the file.
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->paths->getFolder($this->file.'.json');
    }

    /**
     * Get the contents of the file.
     *
     * @return array
     */
    protected function getContents()
    {
        $file = $this->getFilepath();
        $file = $this->bash->getFile($file) ?: '{}';
        $file = (array) json_decode($file, true);

        return $file;
    }

    /**
     * Save the contents of the file.
     *
     * @param array $contents
     */
    protected function saveContents($contents)
    {
        $file = $this->getFilepath();
        $this->bash->putFile($file, json_encode($contents));
    }
}
