<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu;

use remoteprogrammer\simplerpmenu\services\SimpleRpMenuService as SimpleRpMenuServiceService;
use remoteprogrammer\simplerpmenu\services\SimpleRpMenuItemsService as SimpleRpMenuItemsServiceService;
use remoteprogrammer\simplerpmenu\variables\SimpleRpMenuVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 *
 * @property  SimpleRpMenuServiceService $simplerpmenu
 * @property  SimpleRpMenuItemsServiceService $simplerpmenuItems
 */
class SimpleRpMenu extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * SimpleRpMenu::$plugin
     *
     * @var SimpleRpMenu
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * SimpleRpMenu::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();

        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'simplerpmenu',
                    'label' => 'Simple RP Menus',
                    'icon' => '@remoteprogrammer/simplerpmenu/icon.svg'
                ];
            }
        );

        $this->setComponents([
            'simplerpmenu' => services\SimpleRpMenuService::class,
            'simplerpmenuItems' => services\SimpleRpMenuItemsService::class,
        ]);
        self::$plugin = $this;

        // Register our site routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['siteActionTrigger1'] = 'simple-rp-menu/simple-menu-controller';
        //         $event->rules['siteActionTrigger2'] = 'simple-rp-menu/simple-menu-items-controller';
        //     }
        // );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                // $event->rules['cpActionTrigger1'] = 'simple-rp-menu/simple-menu-controller/do-something';
                // $event->rules['cpActionTrigger2'] = 'simple-rp-menu/simple-menu-items-controller/do-something';

                $event->rules['simplerpmenu'] = 'simple-rp-menu/simple-rp-menu';
                $event->rules['simplerpmenu/<siteHandle:\w+>'] = 'simple-rp-menu/simple-rp-menu';
                $event->rules['simplerpmenu/menu-new/<siteHandle:\w+>'] = 'simple-rp-menu/simple-rp-menu/menu-new';
                $event->rules['simplerpmenu/delete-menu'] = 'simple-rp-menu/simple-rp-menu/delete-menu';
                $event->rules['simplerpmenu/delete-menu/<menuId:\d+>'] = 'simple-rp-menu/simple-rp-menu/delete-menu';
                $event->rules['simplerpmenu/menu-edit/<menuId:\d+>'] = 'simple-rp-menu/simple-rp-menu/menu-edit';
                $event->rules['simplerpmenu/menu-edit/'] = 'simple-rp-menu/simple-rp-menu';
                $event->rules['simplerpmenu/menu-items/<menuId:\d+>'] = 'simple-rp-menu/simple-rp-menu-items/edit';
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('simplerpmenu', SimpleRpMenuVariable::class);
            }
        );

        // Do something after we're installed
        // Event::on(
        //     Plugins::class,
        //     Plugins::EVENT_AFTER_INSTALL_PLUGIN,
        //     function (PluginEvent $event) {
        //         if ($event->plugin === $this) {
        //             // We were just installed
        //         }
        //     }
        // );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'simple-rp-menu',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
