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

use remoteprogrammer\simplerpmenu\SimpleRpMenu;
use remoteprogrammer\simplerpmenu\assetbundles\simplerpmenu\SimpleRpMenuAsset;
use remoteprogrammer\simplerpmenu\models\SimpleRpMenuModel;


use Craft;
use craft\web\Controller;

/**
 * SimpleMenuController Controller
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
class SimpleRpMenuController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected  array|int|bool $allowAnonymous = ['index', 'menu-new', 'save-menu', 'delete-menu', 'menu-edit'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/simple-rp-menu/simple-rp-menu
     *
     * @return mixed
     */
    public function actionIndex($siteHandle = null)
    {
        $siteHandle = $siteHandle ?? Craft::$app->getSites()->currentSite->handle;

        $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        if (!$objSite) {
            $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
            $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }
        $this->view->registerAssetBundle(SimpleRpMenuAsset::class);
        $data['menus'] = SimpleRpMenu::$plugin->simplerpmenu->getAllMenus($objSite->id);
        $data['objSite'] = $objSite;

        return $this->renderTemplate('simple-rp-menu/_index', $data);
    }

    /**
     * Handle a request going to our plugin's actionMenuNew URL,
     * e.g.: actions/simple-rp-menu/simple-rp-menu/menu-new
     *
     * @return mixed
     */
    public function actionMenuNew($siteHandle)
    {
        $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        if (!$objSite) {
            $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
            $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }
        $data['objSite'] = $objSite;

        $this->view->registerAssetBundle(SimpleRpMenuAsset::class);

        return $this->renderTemplate('simple-rp-menu/_menu-new', $data);
    }
    /**
     * Handle a request going to our plugin's actionSaveMenu URL,
     * e.g.: actions/simple-rp-menu/simple-rp-menu/save-menu
     *
     * @return mixed
     */
    public function actionSaveMenu()
    {
        $this->requirePostRequest();
        if (isset(Craft::$app->request->getBodyParams()['data']['id'])) {
            $model = SimpleRpMenu::$plugin->simplerpmenu->getMenuById(Craft::$app->request->getBodyParams()['data']['id']);
        } else {
            $model = new SimpleRpMenuModel();
        }

        $model->setAttributes(Craft::$app->request->getBodyParams()['data']);

        if (!$model->validate()) {
            Craft::$app->getSession()->setError(Craft::t('simple-rp-menu', 'Validation errors have occured.'));

            $objSite = Craft::$app->getSites()->getSiteById($model->site_id);
            if (!$objSite) {
                $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
                $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            }

            return $this->renderTemplate('simple-rp-menu/_menu-new', [
                'menu' => $model,
                'errors' => $model->getErrors(),
                'objSite' => $objSite
            ]);
        } else {
            SimpleRpMenu::$plugin->simplerpmenu->saveMenu($model);
            Craft::$app->getSession()->setNotice(Craft::t('simple-rp-menu', 'Menu saved successfully.'));

            $objSite = Craft::$app->getSites()->getSiteById($model->site_id);
            if (!$objSite) {
                $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
            } else {
                $siteHandle = $objSite->handle;
            }
            return $this->redirect("simplerpmenu/$siteHandle");
        }
    }

    public function actionDeleteMenu()
    {
        if (Craft::$app->request->getIsAjax()) {
            $this->requirePostRequest();
            $this->requireAcceptsJson();

            if (SimpleRpMenu::$plugin->simplerpmenu->deleteMenuById(Craft::$app->request->post('menuID'))) {
                // Return data
                $returnData['success'] = true;
                return $this->asJson($returnData);
            };
        } else {
            $menuId = Craft::$app->request->getSegment(3);

            if ($menuId) {
                $menu = SimpleRpMenu::$plugin->simplerpmenu->getMenuById($menuId);
                if (SimpleRpMenu::$plugin->simplerpmenu->deleteMenuById($menuId)) {
                    Craft::$app->getSession()->setNotice(Craft::t('simple-rp-menu', 'Menu deleted successfully.'));
                } else {
                    Craft::$app->getSession()->setError(Craft::t('simple-rp-menu', 'An error occurred while deleting menu.'));
                }
                $objSite = Craft::$app->getSites()->getSiteById($menu->site_id);
                if (!$objSite) {
                    $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
                } else {
                    $siteHandle = $objSite->handle;
                }
                return $this->redirect("simplerpmenu/$siteHandle");
            }

            return $this->redirect("simplerpmenu");
        }
    }

    public function actionMenuEdit($menuId = null)
    {
        if ($menuId) {
            $menu = SimpleRpMenu::$plugin->simplerpmenu->getMenuById($menuId);
            $arrData['menu'] = $menu;

            if (isset(Craft::$app->request->getBodyParams()['data']['id'])) {
                $model = SimpleRpMenu::$plugin->simplerpmenu->getMenuById(Craft::$app->request->getBodyParams()['data']['id']);
                $model->setAttributes(Craft::$app->request->getBodyParams()['data']);

                if (!$model->validate()) {
                    Craft::$app->getSession()->setError(Craft::t('simple-rp-menu', 'Validation errors have occured.'));
                    $arrData['menu'] = $model;
                    $arrData['errors'] = $model->getErrors();
                    $arrData['originalMenu'] = $menu;
                } else {
                    SimpleRpMenu::$plugin->simplerpmenu->saveMenu($model);
                    Craft::$app->getSession()->setNotice(Craft::t('simple-rp-menu', 'Menu saved successfully.'));

                    $arrData['menu'] = $model;
                }
            }

            $objSite = Craft::$app->getSites()->getSiteById($menu->site_id);
            if (!$objSite) {
                $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;
                $objSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            }
            $arrData['objSite'] = $objSite;

            return $this->renderTemplate('simple-rp-menu/_menu-edit', $arrData);
        }
    }
}
