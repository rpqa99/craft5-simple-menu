<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\controllers;

use remoteprogrammer\simplerpmenu\assetbundles\simplerpmenu\SimpleRpMenuItemsAsset;
use remoteprogrammer\simplerpmenu\models\SimpleRpMenuItemsModel;
use remoteprogrammer\simplerpmenu\SimpleRpMenu;

use Craft;
use craft\web\Controller;

/**
 * SimpleMenuItemsController Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleRpMenuItemsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected  array|int|bool $allowAnonymous = ['edit', 'save-menu-items'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/simple-rp-menu/simple-rp-menu-items
     *
     * @return mixed
     */
    public function actionEdit($menuId = null)
    {
        $this->view->registerAssetBundle(SimpleRpMenuItemsAsset::class);
        $menu = SimpleRpMenu::$plugin->simplerpmenu->getMenuById($menuId);
        $data['menu'] = $menu;
        $data['sections'] = SimpleRpMenu::$plugin->simplerpmenuItems->getSectionsWithEntries($menu->site_id);
        $data['menuItemsMarkup'] = SimpleRpMenu::$plugin->simplerpmenuItems->getMenuItemsAdminMarkup($menuId);
        $data['categories'] = Craft::$app
                                   ->categories
                                   ->getAllGroups();
        
        $objSite = Craft::$app->getSites()->getSiteById($menu->site_id);
        if (!$objSite) {
            $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
            $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }
        $data['objSite'] = $objSite;
        return $this->renderTemplate('simple-rp-menu/_menu-items', $data);
    }

    /**
     * Handle a request going to our plugin's actionSave URL,
     * e.g.: actions/simple-rp-menu/simple-rp-menu-items/save-menu-items
     *
     * @return mixed
     */
    public function actionSaveMenuItems()
    {
        $this->requirePostRequest();
        $intMenuId = Craft::$app->request->getBodyParams()['menu-id'];
        $strMenuItems = Craft::$app->request->getBodyParams()['menu-items-serialized'];

        $arrMenuItems = json_decode($strMenuItems, true);

        if (!empty($arrMenuItems)) {
            foreach($arrMenuItems as $order=> $menuItem) {
                $parent_id = 0;

                if (isset($menuItem['parent-id'])){
                    $parent_id = $menuItem['parent-id'];

                    foreach ($arrMenuItems as $element) {
                        if (isset($element['item-id']['html']) && $element['item-id']['html'] == $parent_id) {
                            $parent_id = $element['menu-item-db-id'];
                            $arrMenuItems[$order]['parent-id'] = $parent_id;
                            break;
                        }
                    }
                }
                
                if ($menuItem['item-id']['db'] != null) {
                    $menuItemModel = SimpleRpMenu::$plugin->simplerpmenuItems->getMenuItem($menuItem['item-id']['db']);
                } else {
                    $menuItemModel = new SimpleRpMenuItemsModel();
                }
                $arrData['id']= $menuItem['item-id']['db'];
                $arrData['menu_id']= $intMenuId;
                $arrData['parent_id']= $parent_id;
                $arrData['item_order']= $order;
                $arrData['name']= $menuItem['name'];
                $arrData['entry_id']= (isset($menuItem['entry-id']) ? $menuItem['entry-id'] : '');
                $arrData['custom_url']= (isset($menuItem['custom-url']) ? $menuItem['custom-url'] : '');
                $arrData['class']= (isset($menuItem['class']) ? $menuItem['class'] : '');
                $arrData['class_parent']= (isset($menuItem['class-parent']) ? $menuItem['class-parent'] : '');
                $arrData['data_json']= (isset($menuItem['data-json']) ? $menuItem['data-json'] : '');
                $arrData['target']= (isset($menuItem['target']) ? $menuItem['target'] : '');
                $arrData['noLink']= (isset($menuItem['noLink']) ? $menuItem['noLink'] : '');
                $arrData['customShortContent']= (isset($menuItem['custom-short-content']) ? $menuItem['custom-short-content'] : '');

                $menuItemModel->setAttributes($arrData);

                if ($menuItemModel->validate()) {
                    $menuItemDbId = SimpleRpMenu::$plugin->simplerpmenuItems->saveMenuItem($menuItemModel);
                    if (is_numeric($menuItemDbId)) $arrMenuItems[$order]['menu-item-db-id'] = $menuItemDbId;
                }
            }
        }

        $menuItemsDeleted = Craft::$app->request->getBodyParams()['menu-items-deleted'];
        if (!empty($menuItemsDeleted)) {
            $arrItems = explode(',', $menuItemsDeleted);
            if (!empty($arrItems)) {
                foreach ($arrItems as $intVal) {
                    SimpleRpMenu::$plugin->simplerpmenuItems->deleteMenuItem($intVal);
                }
            }
        }
        Craft::$app->getSession()->setNotice(Craft::t('simple-rp-menu', 'Menu items saved successfully.'));
    }
}
