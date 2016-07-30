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

namespace Rocketeer\Services\Connections\Shell\Modules;

/**
 * Bash module for methods related to checking a command's
 * exit status and successful state.
 */
class Statuses extends AbstractBashModule
{
    /**
     * Check the status of the last command.
     *
     * @return bool
     */
    public function status()
    {
        return $this->getOption('pretend') ? true : $this->modulable->getConnection()->status() === 0;
    }

    /**
     * Whether to consider the results of something valid or not.
     *
     * @param mixed $results
     *
     * @return bool
     */
    public function checkResults($results)
    {
        return is_bool($results) ? $results : $this->status();
    }

    /**
     * Check the status of the last run command, return an error if any.
     *
     * @param string      $error   The message to display on error
     * @param string|null $output  The command's output
     * @param string|null $success The message to display on success
     *
     * @return bool
     */
    public function displayStatusMessage($error, $output = null, $success = null)
    {
        // If all went well
        if ($this->checkResults($output)) {
            if ($success) {
                $this->explainer->success($success);
            }

            return $output || true;
        }

        // Else display the error
        $error = sprintf('An error occured: "%s"', $error);
        if ($output) {
            $error .= ', while running:'.PHP_EOL.$output;
        }

        $this->explainer->error($error);

        return false;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'checkResults',
            'displayStatusMessage',
            'status',
        ];
    }
}
