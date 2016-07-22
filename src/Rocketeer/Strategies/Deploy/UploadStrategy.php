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

use Rocketeer\Strategies\AbstractStrategy;
use Symfony\Component\Finder\Finder;

class UploadStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * Prepare a release and mark it as deployed.
     */
    public function deploy()
    {
        $localPath = $this->on('dummy', function () {
            $this->executeTask('CreateRelease');

            // $this->executeTask('Dependencies');

            return $this->releasesManager->getCurrentReleasePath();
        });

        dump($localPath, $this->paths->getFolder());

        return $this->uploadTo($localPath, $this->paths->getFolder());
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
            } elseif ($file->getMTime() >= $this->getConnection()->getTimestamp($path)) {
                $this->put($path, $file->getContents());
            }

            $this->command->progressAdvance();
        }

        $this->command->progressAdvance();
    }
}
