<?php
namespace Rocketeer\Services\Connections\Shell\Modules;

class Statuses extends AbstractBashModule
{

    /**
     * Check the status of the last command.
     *
     * @return bool
     */
    public function status()
    {
        return $this->getOption('pretend') ? true : $this->getConnection()->status() === 0;
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
        if ($this->status()) {
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
            'status',
            'displayStatusMessage',
        ];
    }
}
