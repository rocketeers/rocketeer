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
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Services\Events\Listeners\TaggableListenerInterface;
use Rocketeer\Services\Events\Listeners\TaggableListenerTrait;
use Rocketeer\Services\Roles\HasRolesInterface;
use Rocketeer\Services\Roles\HasRolesTrait;
use Rocketeer\Traits\Properties\ConfigurableTrait;
use Rocketeer\Traits\Properties\HasEventsTrait;
use Rocketeer\Traits\SluggableTrait;
use Rocketeer\Traits\StepsRunnerTrait;

/**
 * An abstract AbstractTask with common helpers, from which all Tasks derive.
 */
abstract class AbstractTask extends Bash implements HasRolesInterface, IdentifierInterface, TaggableListenerInterface
{
    use ConfigurableTrait;
    use HasEventsTrait;
    use HasRolesTrait;
    use StepsRunnerTrait;
    use SluggableTrait;
    use TaggableListenerTrait;

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

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @var bool
     */
    protected $local = false;

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
        $this->name = is_string($name) ? ucfirst($name) : $this->name;
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
     * @return mixed
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

        return $this->runWithBeforeAfterEvents(function () {
            return $this->local ? $this->on('local', [$this, 'execute']) : $this->execute();
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
