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

/**
 * Deploys locally and uploads the files through FTP.
 */
class UploadStrategy extends AbstractLocalDeployStrategy
{
    /**
     * @var string
     */
    protected $description = 'Deploys locally and uploads the files through FTP';

    /**
     * @param string $from
     * @param string $to
     *
     * @return mixed
     */
    protected function onReleaseReady($from, $to)
    {
        $this->explainer->comment(sprintf('Uploading files from %s to %s', $from, $to));

        // Only gather files from existing folders (for pretend mode)
        $folders = array_filter([$from], 'is_dir');
        if (!$folders) {
            return true;
        }

        $files = (new Finder())->in($folders)->exclude(['.git']);
        $this->command->progressStart(iterator_count($files));
        foreach ($files as $file) {
            $this->command->progressAdvance();
            $path = $to.DS.str_replace($from, null, $file->getPathname());

            if ($file->isDir()) {
                $this->createFolder($path);
            } else {
                $this->put($path, $file->getContents());
            }
        }

        $this->command->progressFinish();

        return true;
    }
}
