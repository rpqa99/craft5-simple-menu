<?php

namespace remoteprogrammer\simplerpmenu\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\Category;

class DynamicMenuService extends Component
{
    public function resolveDynamicItems($menuItems)
    {
        $resolvedItems = [];

        foreach ($menuItems as $item) {
            // First, process any static children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->resolveDynamicItems($item['children']);
            } else {
                $item['children'] = [];
            }

            if (isset($item['dropdownType']) && $item['dropdownType'] === 'dynamic') {
                $dynamicChildren = $this->fetchDynamicChildren($item);
                
                $dynamicSettings = json_decode($item['dynamicSettings'] ?? '{}', true);
                $dynamicPosition = $dynamicSettings['dynamicPosition'] ?? 'afterStatic';

                if ($dynamicPosition === 'beforeStatic') {
                    $item['children'] = array_merge($dynamicChildren, $item['children']);
                } elseif ($dynamicPosition === 'replaceStatic') {
                    $item['children'] = $dynamicChildren;
                } else {
                    // afterStatic (default)
                    $item['children'] = array_merge($item['children'], $dynamicChildren);
                }
            }

            $resolvedItems[] = $item;
        }

        return $resolvedItems;
    }

    private function fetchDynamicChildren($item)
    {
        $maxLevel = isset($item['maxLevel']) ? (int)$item['maxLevel'] : 1;
        $entryId = $item['entry_id'];
        $dynamicSource = $item['dynamicSource'] ?? '';
        
        if (empty($entryId)) {
            return [];
        }

        // Check if it's an entry (structure) or category
        $element = Entry::find()->id($entryId)->one();
        if (!$element) {
            $element = Category::find()->id($entryId)->one();
        }

        if (!$element) {
            return [];
        }

        return $this->getDescendantsAsMenuItems($element, $maxLevel, 1, $dynamicSource);
    }

    private function getDescendantsAsMenuItems($element, $maxLevel, $currentLevel, $dynamicSource)
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $childrenElements = [];

        // 1. Get Structural Descendants (Subcategories)
        if (method_exists($element, 'getChildren')) {
            $query = $element->getChildren();
            if ($query) {
                $childrenElements = $query->all();
            }
        }

        // 2. Get Related Entries (Books) if dynamicSource is 'entries'
        // ONLY fetch related entries if the current element is a Structure or Category.
        // DO NOT fetch related entries for leaf Entries (like Books) to prevent infinite menus.
        $isStructureOrCategory = ($element instanceof \craft\elements\Category) || 
                                 ($element instanceof \craft\elements\Entry && $element->section->type === 'structure');

        if ($dynamicSource === 'entries' && $isStructureOrCategory) {
            // ONLY fetch entries pointing TO this element (e.g. Books pointing to the Category)
            $relatedEntries = Entry::find()->relatedTo(['targetElement' => $element])->all();
            
            $existingIds = array_map(function($e) { return $e->id; }, $childrenElements);
            
            foreach ($relatedEntries as $related) {
                if (!in_array($related->id, $existingIds)) {
                    $childrenElements[] = $related;
                }
            }
        }

        $menuItems = [];

        foreach ($childrenElements as $child) {
            $menuItem = [
                'id' => 'dyn_' . $child->id,
                'name' => $child->title,
                'custom_url' => $child->url,
                'entry_id' => $child->id,
                'dropdownType' => 'static', // children are just static links
                'class' => '',
                'class_parent' => '',
                'target' => '_self',
                'noLink' => false,
                'customShortContent' => '',
                'data_json' => '',
                'isMegaMenu' => false,
                'children' => $this->getDescendantsAsMenuItems($child, $maxLevel, $currentLevel + 1, $dynamicSource)
            ];
            
            $menuItems[] = $menuItem;
        }

        return $menuItems;
    }
}
