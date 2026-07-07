<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\services;

use remoteprogrammer\simplerpmenu\models\SimpleRpMenuItemsModel;
use remoteprogrammer\simplerpmenu\SimpleRpMenu;
use remoteprogrammer\simplerpmenu\records\SimpleRpMenuItemsRecord;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\Category;

/**
 * SimpleRpMenuItemsService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleRpMenuItemsService extends Component
{
    // Public Properties
    // =========================================================================

    public function getSectionsWithEntries($site_id) {
        $sections = $this->getSections($site_id);

        if ($sections) {
            foreach($sections as $handle => $values) {
                if (!empty($sections[$handle])) {
                    foreach ($values as $index => $value) {
                        $sections[$handle][$index]['entries'] = $this->getFirstEntriesBySection($value['handle'], $site_id);
                    }
                }
            }
        }

        return $sections;
    }

    public function getMenuItem($id) {
        $record = SimpleRpMenuItemsRecord::findOne([
            'id' => $id
        ]);
        return new SimpleRpMenuItemsModel($record->getAttributes());
    }

    public function saveMenuItem(SimpleRpMenuItemsModel $model) {
        $record = false;
        if (isset($model->id)) {
            $record = SimpleRpMenuItemsRecord::findOne( [
                'id' => $model->id
            ]);
        }

        if (!$record) {
            $record = new SimpleRpMenuItemsRecord();
        }

        $record->menu_id = $model->menu_id;
        $record->parent_id = $model->parent_id;
        $record->item_order = $model->item_order;
        $record->name = $model->name;
        $record->entry_id = $model->entry_id;
        $record->custom_url = $model->custom_url;
        $record->class = $model->class;
        $record->class_parent = $model->class_parent;
        $record->data_json = $model->data_json;
        $record->target = $model->target;
        $record->noLink = $model->noLink;
        $record->customShortContent = $model->customShortContent;
        $record->isMegaMenu = $model->isMegaMenu;
        $record->dropdownType = $model->dropdownType;
        $record->dynamicSource = $model->dynamicSource;
        $record->dynamicSourceId = $model->dynamicSourceId;
        $record->maxLevel = $model->maxLevel;
        $record->dynamicSettings = $model->dynamicSettings;

        $save = $record->save();
        if ( !$save ) {
            Craft::getLogger()->log( $record->getErrors(), LOG_ERR, 'simple-rp-menu' );
        }
        return $record->id;
    }

    public function deleteMenuItem($id) {
        $record = SimpleRpMenuItemsRecord::findOne([
            'id' => $id
        ]);

        if ($record) {
            if ($record->delete()) {
                return 1;
            };
        }
    }

    public function deleteItemsByMenuId($record) {
        $records = SimpleRpMenuItemsRecord::findAll([
            'menu_id' => $record->id,
        ]);

        foreach ($records as $record) {
            $record->delete();
        }
        return;
    }

    public function getMenuItems($menuId) {
        $arrMenuItems = [];

        $menuItems = SimpleRpMenuItemsRecord::find()
                        ->where(['menu_id' => $menuId])
                        ->orderBy('item_order')
                        ->all();

        foreach ($menuItems as $intKey => $objItem) {
            $arrMenuItems[$intKey]['id'] = $objItem->id;
            $arrMenuItems[$intKey]['menu_id'] = $objItem->menu_id;
            $arrMenuItems[$intKey]['parent_id'] = $objItem->parent_id;
            $arrMenuItems[$intKey]['item_order'] = $objItem->item_order;
            $arrMenuItems[$intKey]['name'] = $objItem->name;
            $arrMenuItems[$intKey]['entry_id'] = $objItem->entry_id;
            $arrMenuItems[$intKey]['custom_url'] = $objItem->custom_url;
            $arrMenuItems[$intKey]['class'] = $objItem->class;
            $arrMenuItems[$intKey]['class_parent'] = $objItem->class_parent;
            $arrMenuItems[$intKey]['data_json'] = $objItem->data_json;
            $arrMenuItems[$intKey]['target'] = $objItem->target;
            $arrMenuItems[$intKey]['noLink'] = $objItem->noLink;
            $arrMenuItems[$intKey]['customShortContent'] = $objItem->customShortContent;
            $arrMenuItems[$intKey]['isMegaMenu'] = $objItem->isMegaMenu;
            $arrMenuItems[$intKey]['dropdownType'] = $objItem->dropdownType;
            $arrMenuItems[$intKey]['dynamicSource'] = $objItem->dynamicSource;
            $arrMenuItems[$intKey]['dynamicSourceId'] = $objItem->dynamicSourceId;
            $arrMenuItems[$intKey]['maxLevel'] = $objItem->maxLevel;
            $arrMenuItems[$intKey]['dynamicSettings'] = $objItem->dynamicSettings;
        }

        if ($arrMenuItems) {
            return $this->sortMenuItemsByParents($arrMenuItems);
        }
        return $arrMenuItems;
    }

    public function getResolvedMenuItems($menuId) {
        $cache = Craft::$app->getCache();
        $cacheKey = 'simple-rp-menu-resolved-' . $menuId;

        $cachedItems = $cache->get($cacheKey);
        if ($cachedItems !== false) {
            return $cachedItems;
        }

        $items = $this->getMenuItems($menuId);
        
        // Populate standard Entry/Category URLs to avoid n+1 queries in Twig
        foreach ($items as &$item) {
            $this->populateItemUrl($item);
        }
        
        $resolvedItems = SimpleRpMenu::$plugin->dynamicMenuService->resolveDynamicItems($items);
        
        $cache->set($cacheKey, $resolvedItems, 86400, new \yii\caching\TagDependency(['tags' => 'simple-rp-menu']));

        return $resolvedItems;
    }

    private function populateItemUrl(&$item) {
        if (empty($item['custom_url']) && !empty($item['entry_id'])) {
            $element = Entry::find()->id($item['entry_id'])->one();
            if (!$element) {
                $element = Category::find()->id($item['entry_id'])->one();
            }
            if ($element) {
                $item['custom_url'] = $element->url;
            }
        }
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child) {
                $this->populateItemUrl($child);
            }
        }
    }

    public function getMenuItemsAdminMarkup($menuId) {
        $localHTML = '';
        $arrMenuItems = $this->getMenuItems($menuId);

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuItem) {
                $localHTML .= $this->getItemAdminMarkup($menuItem);
            }
        }
        return $localHTML;
    }

    private function getSections($site_id) {
        $sections = [];

        $sections['single'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'single', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        $sections['structure'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'structure', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        $sections['channel'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'channel', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        return $sections;
    }

    private function getEntriesBySection($handle, $site_id) {
        return Entry::find()
                    ->section($handle)
                    ->siteId($site_id)
                    ->all();
    }

    private function getFirstEntriesBySection($handle, $site_id) {
        return Entry::find()
                    ->section($handle)
                    ->siteId($site_id)
                    ->one();
    }

    private function sortMenuItemsByParents($arrMenuItems) {
        $counter = 0;
        $arrMenuItemsSorted = [];

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuItem) {
                if ($menuItem['parent_id'] == 0) {
                    $arrMenuItemsSorted[] = $menuItem;
                }
            }
        }

        if ($arrMenuItemsSorted) {
            foreach ($arrMenuItemsSorted as $menuItem){
                $arrMenuItemsSorted[$counter] = $this->addChildToParent($arrMenuItems,$menuItem);
                $counter++;
            }
        }
        return $arrMenuItemsSorted;
    }
    
    private function addChildToParent($arrMenuItems, $menuItem) {
        $parent_id = $menuItem['id'];

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuSubItem) {
                if ($menuSubItem['parent_id'] == $parent_id) {
                    $menuSubItem = $this->addChildToParent($arrMenuItems,$menuSubItem);
                    $menuItem['children'][] = $menuSubItem;
                }
            }
        }
        return $menuItem;
    }

    private function getItemAdminMarkup($menuItem) {
        $localHTML = '';

        $entry = Entry::find()
            ->id($menuItem['entry_id'])
            ->one();

        if (!$entry) {
            $entry = Category::find()
                ->id($menuItem['entry_id'])
                ->one();
        }

        $localHTML .= '<li id="menu-item-' .$menuItem['id']. '">';
            $localHTML .= '<div>';
                $localHTML .= '<div class="item-heading">';
                    $localHTML .= '<span class="settings-toggle"></span>';
                    $localHTML .= '<span class="menu-title">' . $menuItem['name'] . '</span>';
                    $localHTML .= '<span class="delete-menu btn small" data-id="' .$menuItem['id']. '">Delete</span>';
                $localHTML .= '</div>';
                $localHTML .= '<div class="item-content">';
                $localHTML .= '<input type="hidden" name="item-id" value="' .$menuItem['id']. '" />';
                    if ($menuItem['custom_url'] == '') $localHTML .= '<input type="hidden" name="item-entry-id" value="' .$menuItem['entry_id']. '" />';
                    $localHTML .= '<div class="inner">';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Name') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="item-name" value="' .$menuItem['name']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Without Link ?') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="noLink-'.$menuItem['id'].'" class="text nicetext fullwidth noLink-menu" name="noLink">';
                                    $localHTML .= '<option value="0" '.(($menuItem['noLink']=='0') ? 'selected' : '') .' >No</option>';
                                    $localHTML .= '<option value="1" '.(($menuItem['noLink']=='1') ? 'selected' : '') .'>Yes</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Is Mega Menu ?') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="isMegaMenu-'.$menuItem['id'].'" class="text nicetext fullwidth isMegaMenu-menu" name="isMegaMenu">';
                                    $localHTML .= '<option value="0" '.((isset($menuItem['isMegaMenu']) && $menuItem['isMegaMenu']=='0') ? 'selected' : '') .' >No</option>';
                                    $localHTML .= '<option value="1" '.((isset($menuItem['isMegaMenu']) && $menuItem['isMegaMenu']=='1') ? 'selected' : '') .'>Yes</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Menu Type') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="dropdownType-'.$menuItem['id'].'" class="text nicetext fullwidth" name="dropdown-type" onchange="toggleDynamicFields('.$menuItem['id'].', this.value)">';
                                    $dropdownType = isset($menuItem['dropdownType']) ? $menuItem['dropdownType'] : 'static';
                                    $localHTML .= '<option value="static" '.($dropdownType == 'static' ? 'selected' : '') .'>Static (Default)</option>';
                                    $localHTML .= '<option value="dynamic" '.($dropdownType == 'dynamic' ? 'selected' : '') .'>Dynamic</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field dynamic-menu-options-'.$menuItem['id'].'" style="'.($dropdownType == 'dynamic' ? '' : 'display:none;').'">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Dynamic Source') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="dynamicSource-'.$menuItem['id'].'" class="text nicetext fullwidth" name="dynamic-source">';
                                    $dynamicSource = isset($menuItem['dynamicSource']) ? $menuItem['dynamicSource'] : '';
                                    $localHTML .= '<option value="entries" '.($dynamicSource == 'entries' ? 'selected' : '') .'>Entries (Section)</option>';
                                    $localHTML .= '<option value="categories" '.($dynamicSource == 'categories' ? 'selected' : '') .'>Categories</option>';
                                    $localHTML .= '<option value="structure" '.($dynamicSource == 'structure' ? 'selected' : '') .'>Structure</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field dynamic-menu-options-'.$menuItem['id'].'" style="'.($dropdownType == 'dynamic' ? '' : 'display:none;').'">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Max Level (Depth)') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="maxLevel-'.$menuItem['id'].'" class="text nicetext fullwidth" name="max-level">';
                                    $maxLevel = isset($menuItem['maxLevel']) ? $menuItem['maxLevel'] : 1;
                                    for ($i = 1; $i <= 5; $i++) {
                                        $localHTML .= '<option value="'.$i.'" '.($maxLevel == $i ? 'selected' : '') .'>'.$i.'</option>';
                                    }
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field dynamic-menu-options-'.$menuItem['id'].'" style="'.($dropdownType == 'dynamic' ? '' : 'display:none;').'">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Dynamic Position') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="dynamicPosition-'.$menuItem['id'].'" class="text nicetext fullwidth" name="dynamic-position">';
                                    $dynamicSettings = json_decode($menuItem['dynamicSettings'] ?? '{}', true);
                                    $dynamicPosition = $dynamicSettings['dynamicPosition'] ?? 'afterStatic';
                                    $localHTML .= '<option value="beforeStatic" '.($dynamicPosition == 'beforeStatic' ? 'selected' : '') .'>Before Static Children</option>';
                                    $localHTML .= '<option value="afterStatic" '.($dynamicPosition == 'afterStatic' ? 'selected' : '') .'>After Static Children</option>';
                                    $localHTML .= '<option value="replaceStatic" '.($dynamicPosition == 'replaceStatic' ? 'selected' : '') .'>Replace Static Children</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        // $localHTML .= '<div class="row field">';
                        //     $localHTML .= '<div class="heading">';
                        //         $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Display Short Content ?') . ':</label>';
                        //     $localHTML .= '</div>';
                        //     $localHTML .= '<div class="input">';
                        //         $localHTML .= '<select id="customShortContent-'.$menuItem['id'].'" class="text nicetext fullwidth" name="customShortContent">';
                        //             $localHTML .= '<option value="0" '.(($menuItem['hasShortDescp']=='0') ? 'selected' : '') .' >No</option>';
                        //             $localHTML .= '<option value="1" '.(($menuItem['hasShortDescp']=='1') ? 'selected' : '') .'>Yes</option>';
                        //         $localHTML .= '</select>';
                        //     $localHTML .= '</div>';
                        // $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Menu Custom Short Content') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<textarea class="text nicetext fullwidth" name="custom-short-content">' .$menuItem['customShortContent']. '</textarea>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        if ($menuItem['entry_id'] == '') {
                            $localHTML .= '<div class="row field">';
                                $localHTML .= '<div class="heading">';
                                    $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Custom URL') . ':</label>';
                                $localHTML .= '</div>';
                                $localHTML .= '<div class="input">';
                                    $localHTML .= '<input class="text nicetext fullwidth" type="text" name="custom-url" value="' .$menuItem['custom_url']. '" />';
                                $localHTML .= '</div>';
                            $localHTML .= '</div>';
                        }
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Class') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="class" value="' .$menuItem['class']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Class parent') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="class-parent" value="' .$menuItem['class_parent']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Data JSON') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<textarea class="text nicetext fullwidth" name="data-json">' .$menuItem['data_json']. '</textarea>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Target options') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="target-'.$menuItem['id'].'" class="text nicetext fullwidth" name="target">';
                                    $localHTML .= '<option value="_self" '.(($menuItem['target']=='_self') ? 'selected' : '') .' >Open in same tab</option>';
                                    $localHTML .= '<option value="_blank" '.(($menuItem['target']=='_blank') ? 'selected' : '') .'>Open in new tab</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        if ($menuItem['custom_url'] == '') {
                            $localHTML .= '<div class="row field">';
                                $localHTML .= '<div class="heading">';
                                    if ($entry) $localHTML .= '<label>' . Craft::t('simple-rp-menu', 'Original') . ':</label> <a href="' . $entry->url . '" target="_blank">' . $entry->title . '</a>';
                                $localHTML .= '</div>';
                            $localHTML .= '</div>';
                        }
                    $localHTML .= '</div>';
                $localHTML .= '</div>';
            $localHTML .= '</div>';
            if (isset($menuItem['children'])) {
                $localHTML .= '<ol>';
                    foreach ($menuItem['children'] as $child) {
                       $localHTML .= $this->getItemAdminMarkup($child); 
                    }
                $localHTML .= '</ol>';
            }
        $localHTML .= '</li>'; 

        return $localHTML;
    }
}
