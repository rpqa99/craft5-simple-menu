<?php
namespace remoteprogrammer\simplerpmenu\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use remoteprogrammer\simplerpmenu\records\SimpleRpMenuRecord;

class RpMenuField extends Field implements PreviewableFieldInterface
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Simple RP Menu Selector';
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ElementInterface $element = null): string
    {
        $options = [
            ['label' => '--- Select a Menu ---', 'value' => '']
        ];

        try {
            $menus = SimpleRpMenuRecord::find()->orderBy('name ASC')->all();
            foreach ($menus as $menu) {
                $options[] = [
                    'label' => $menu->name,
                    'value' => $menu->handle,
                ];
            }
        } catch (\Throwable $e) {
            Craft::error('Could not fetch simple rp menus: ' . $e->getMessage(), __METHOD__);
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/select', [
            'name' => $this->handle,
            'value' => $value,
            'options' => $options,
        ]);
    }
}
