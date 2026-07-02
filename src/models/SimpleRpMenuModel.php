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
use craft\validators\HandleValidator;
use craft\validators\StringValidator;

/**
 * SimpleRpMenuModel Model
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
class SimpleRpMenuModel extends Model
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
     * Name attribute
     *
     * @var string
     */
    public $name;

    /**
     * Handle attribute
     *
     * @var string
     */
    public $handle;

    public $dateCreated;

    public $dateUpdated;

    public $uid;

    /**
     * Site Id attribute
     *
     * @var int
     */
    public $site_id;

    // Public Methods
    // =========================================================================

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
            [['id', 'site_id'], 'integer'],
            [['name', 'handle'], 'string'],
            [['name', 'handle'], 'required'],
            ['handle', 'validateHandle'],
            ['name', 'validateName'],
        ];
    }

    public function validateHandle() {

        $validator = new HandleValidator();
        $validator->validateAttribute($this, 'handle');
        $data = SimpleRpMenu::$plugin->simplerpmenu->getMenuByHandle($this->handle);
        if ($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('simple-rp-menu', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));
        }

    }
    
    public function validateName() {

        $validator = new StringValidator();
        $validator->validateAttribute($this, 'name');
        $data = SimpleRpMenu::$plugin->simplerpmenu->getMenuByName($this->name);
        if ($data && $data->id != $this->id) {
            $this->addError('name', Craft::t('simple-rp-menu', 'Name "{name}" is already in use', ['name' => $this->name]));
        }

    }
}
