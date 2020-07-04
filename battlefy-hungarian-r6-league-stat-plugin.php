<?php
namespace Grav\Plugin;

require_once __DIR__ . '/vendor/autoload.php';

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class BattlefyHungarianR6LeagueStatPluginPlugin
 * @package Grav\Plugin
 */
class BattlefyHungarianR6LeagueStatPluginPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onShortcodeHandlers' => ['onShortcodeHandlers', 0]
        ];
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            // Put your main events here
        ]);
    }

    public function onShortcodeHandlers(Event $e)
    {
        $this->grav['shortcode']->registerShortcode('BattlefyShortCodeHandler.php', __DIR__);
    }
}
