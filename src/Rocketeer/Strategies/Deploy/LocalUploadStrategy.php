<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Strategies\Deploy;

use Symfony\Component\Finder\Finder;

class LocalUploadStrategy extends LocalCloneStrategy
{
    /**
     * @var string
     */
    protected $description = 'Uses FTP to upload a release from a locally cloned repository';

    /**
     * Deploy a new clean copy of the application.
     *
     * @param string|null $destination
     *
     * @return bool
     */
    public function deploy($destination = null)
    {
        if (!$destination) {
            $destination = $this->paths->getFolder();
        }

        // Clone application locally
        $localPath = '';
        $this->on('dummy', function() use (&$localPath) {
            /** @var CloneStrategy $strategy */
            $strategy = $this->getStrategy('Deploy', 'Clone');
            $strategy->deploy();

//            $this->executeTask('Dependencies');
            $this->executeTask('SwapSymlink');

            $localPath = $this->releasesManager->getCurrentReleasePath();
        });

        return $this->uploadTo($localPath, $destination);
    }

    /**
     * @param string $from
     * @param string $to
     */
    protected function uploadTo($from, $to)
    {
        $this->explainer->comment('Uploading files to server');
        $files = (new Finder())->in($from)->exclude(['.git']);
        $this->command->progressStart(iterator_count($files));
        foreach ($files as $file) {
            $path = $file->getPathname();
            $path = str_replace($from, null, $path);
            $path = $to.DS.$path;

            if ($file->isDir()) {
                $this->createFolder($path);
            } elseif (!$this->getConnection()->has($path)) {
                $this->put($path, $file->getContents());
            } elseif($file->getMTime() >= $this->getConnection()->getTimestamp($path)) {
                $this->put($path, $file->getContents());
            }

            $this->command->progressAdvance();
        }

        $this->command->progressAdvance();
    }
}
