<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\models;

use remoteprogrammer\simplerpmenu\SimpleRpMenu;

use Craft;
use craft\base\Model;

/**
 * SimpleRpMenuItemsModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleRpMenuItemsModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Id attribute
     *
     * @var int
     */
    public $id;

    /**
     * Menu_id attribute
     *
     * @var int
     */
    public $menu_id;

    /**
     * Parent_id attribute
     *
     * @var int
     */
    public $parent_id;

    /**
     * Item_order attribute
     *
     * @var int
     */
    public $item_order;

    /**
     * Name attribute
     *
     * @var string
     */
    public $name;

    /**
     * Entry_id attribute
     *
     * @var int
     */
    public $entry_id;

     /**
     * Custom_url attribute
     *
     * @var string
     */
    public $custom_url;

    /**
     * Class attribute
     *
     * @var string
     */
    public $class;

    /**
     * Class_parent attribute
     *
     * @var string
     */
    public $class_parent;

    /**
     * Data_json attribute
     *
     * @var string
     */
    public $data_json;

    /**
     * Target attribute
     *
     * @var string
     */
    public $target;

    public $dateCreated;

    public $dateUpdated;

    public $uid;
    
    public $noLink;
    public $customShortContent;

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            [['id', 'menu_id', 'parent_id', 'item_order', 'entry_id'], 'integer'],
            [['noLink', 'customShortContent'], 'safe'],
            [['name', 'custom_url', 'class', 'class_parent', 'data_json', 'target'], 'string'],
            [['menu_id', 'parent_id', 'item_order', 'name'], 'required'],
        ];
    }
}
