<?php

/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\variables;

use remoteprogrammer\simplerpmenu\SimpleRpMenu;

use Craft;

/**
 * Simple RP Menu Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.simpleRpMenu }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleRpMenuVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.simpleRpMenu.getMenuHTML }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.simpleRpMenu.getMenuHTML(twigValue) }}
     *
     * @param null $handle
     * @param array $config
     * 
     * @return string
     */

    public function getRpMenuHTML($handle, $config = array())
    {
        if ($handle != '') {
            return SimpleRpMenu::$plugin->simplerpmenu->getMenuHTML($handle, $config);
        }
    }
}
