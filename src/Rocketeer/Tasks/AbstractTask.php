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

namespace Rocketeer\Tasks;

use DateTime;
use League\Event\ListenerInterface;
use Rocketeer\Bash;
use Rocketeer\Interfaces\HasRolesInterface;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Traits\Properties\Configurable;
use Rocketeer\Traits\Properties\HasEvents;
use Rocketeer\Traits\Properties\HasRoles;
use Rocketeer\Traits\Sluggable;
use Rocketeer\Traits\StepsRunner;

/**
 * An abstract AbstractTask with common helpers, from which all Tasks derive.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractTask extends Bash implements HasRolesInterface, IdentifierInterface, ListenerInterface
{
    use Configurable;
    use HasEvents;
    use HasRoles;
    use StepsRunner;
    use Sluggable;

    /**
     * The name of the task.
     *
     * @var string
     */
    protected $name;

    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description;

    /**
     * A set of options that guide the entity.
     *
     * @var array
     */
    protected $options = [];

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// REFLECTION //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get a global identifier for this entity.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'tasks.'.$this->getSlug();
    }

    /**
     * Get what the task does.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Change the task's name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = ucfirst($name) ?: $this->name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description ?: $this->description;
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// EXECUTION ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Run the task.
     *
     * @return string
     */
    abstract public function execute();

    /**
     * Fire the command.
     *
     * @return bool
     */
    public function fire()
    {
        $this->displayStatus();
        $callback = function () {
            return $this->execute();
        };

        return $this->runWithBeforeAfterEvents(function () use ($callback) {
            return $this->local ? $this->onLocal($callback) : $callback();
        });
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Display a list of releases and their status.
     *
     * @codeCoverageIgnore
     */
    protected function displayReleases()
    {
        if (!$this->command) {
            return;
        }

        $key = 0;
        $rows = [];
        $releases = $this->releasesManager->getValidationFile();

        // Append the rows
        foreach ($releases as $name => $state) {
            $icon = $state ? '✓' : '✘';
            $color = $state ? 'green' : 'red';
            $date = DateTime::createFromFormat('YmdHis', $name)->format('Y-m-d H:i:s');
            $date = sprintf('<fg=%s>%s</fg=%s>', $color, $date, $color);

            // Add color to row
            $rows[] = [$key, $name, $date, $icon];
            ++$key;
        }

        // Render table
        $this->command->comment('Here are the available releases :');
        $this->command->table(
            ['#', 'Path', 'Deployed at', 'Status'],
            $rows
        );

        return $rows;
    }

    /**
     * Display what the command is and does.
     */
    protected function displayStatus()
    {
        $name = $this->getName();
        $description = $this->getDescription();
        $time = $this->timer->getTime($this);
        $event = $this->event ? $this->event->getName() : null;

        $this->explainer->display($name, $description, $event, $time);
    }
}
