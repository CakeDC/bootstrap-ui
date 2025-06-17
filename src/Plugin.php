<?php
declare(strict_types=1);

namespace BootstrapUI;

use Cake\Core\BasePlugin;

/**
 * Plugin class for BootstrapUI
 */
class Plugin extends BasePlugin
{
    /**
     * The name of this plugin.
     *
     * @var string|null
     */
    protected ?string $name = 'BootstrapUI';

    /**
     * Do bootstrapping or not.
     *
     * @var bool
     */
    protected bool $bootstrapEnabled = false;

    /**
     * Load routes or not.
     *
     * @var bool
     */
    protected bool $routesEnabled = false;

    /**
     * Enable console commands or not.
     *
     * @var bool
     */
    protected bool $consoleEnabled = false;

    /**
     * Enable middleware or not.
     *
     * @var bool
     */
    protected bool $middlewareEnabled = false;

    /**
     * Register container services or not.
     *
     * @var bool
     */
    protected bool $servicesEnabled = false;

    /**
     * Load events or not.
     *
     * @var bool
     */
    protected bool $eventsEnabled = false;
}
