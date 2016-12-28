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
        $format = $this->explainer->getProgressBarFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'.PHP_EOL.
            '%message%'
        );

        // Create progress bar
        $progress = $this->command->createProgressBar(iterator_count($files));
        $progress->setFormat($format);
        $progress->start();

        foreach ($files as $file) {
            $origin = str_replace($from, null, $file->getPathname());
            $path = $to.DS.str_replace($from, null, $file->getPathname());
            $progress->setMessage(
                dirname($origin).DS.
                '<info>'.basename($origin).'</info>'
            );

            if ($file->isDir()) {
                $this->createFolder($path);
            } else {
                $this->put($path, $file->getContents());
            }

            $progress->advance();
        }

        $progress->finish();

        return true;
    }
}
