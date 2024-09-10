/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * MenuItem Field JS
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

$(document).ready(function() {
    $('.os-accordion').accordion({
        heightStyle: "content",
        collapsible: true,
        active: false
    });

    $('.os-tabs').tabs();

    $('.search').filterList();

    var menuList = $('ol#menu-list'),
        menuListConfig = {
            handle: 'div',
            items: 'li',
            toleranceElement: '> div',
            isTree: true,
            forcePlaceholderSize: true,
            placeholder: 'placeholder'
        };

    menuList.nestedSortable(menuListConfig);

    var deletedMenuItems = $('#menu-items-deleted');

    menuList.on('click', '.delete-menu', function() {
        if (confirm(Craft.t('simple-rp-menu','Are you sure you want to delete this menu item?'))) {
            var element = $(this),
                targetToDeleteID = element.attr('data-id'),
                targetToDelete = $('#menu-item-' + targetToDeleteID);
                targetChildren = targetToDelete.find('ol');
                deletedMenuItemsValue = deletedMenuItems.val();

            if ( deletedMenuItemsValue == '' ) deletedMenuItemsValue = [];
            else deletedMenuItemsValue = deletedMenuItemsValue.split(',')

            deletedMenuItemsValue.push(targetToDeleteID);
            deletedMenuItems.val(deletedMenuItemsValue.toString());

            if (targetChildren.length) {
                var childrenHTML = targetChildren.html();
                targetChildren.remove();
                targetToDelete.before($(childrenHTML));
            }

            targetToDelete.remove();

            if ( menuList.find('li').length == 0 ) $('#menu-list-placeholder').show();
        }
    });

    /**show and hide custom URL fields */
    
    // menuList.on('click', '.noLink-menu', function() {
    //     var element = $(this),
    //     selItemVal = element.val(),
    //     targetItemID = element.attr('data-id'),
    //     targetURL = $('#custom-url-wrapper-' + targetItemID);
    //     targetTarget= $('#target-' + targetItemID);
    //     if(selItemVal >0){
    //         $(targetURL, targetTarget).show();
    //     }else{
    //         $(targetURL, targetTarget).hide();
    //     }
    // });

    menuList.on('click', '.settings-toggle', function(){
        if ( !$(this).hasClass('ui-sortable-helper') ) $(this).closest('li').toggleClass('active');
    });

    var inputCounter;
    if ( $('#menu-list li').length == 0 ) inputCounter = 1;
    else {
        var biggestID = 0;
        $('#menu-list li').each(function(){
            var elementID = $(this).attr('id'),
                elementIDNumber = parseInt(elementID.replace('menu-item-', ''));

            if ( elementIDNumber > biggestID ) biggestID = elementIDNumber;
        });

        inputCounter = biggestID + 1;
    }

    $('#menu-items-sidebar form').each(function(){
        var form = $(this);

        form.submit(function(e) {
            e.preventDefault();

            $('#menu-list-placeholder').hide();

            var inputs = form.find('input[type="checkbox"]'),
                values = [];

            inputs.each(function(){
                var input = $(this);

                if (input.is(':checked')) {
                    var itemID = input.val(),
                        itemURL = input.attr('data-url'),
                        itemName = input.next().text(),
                        itemHTML = '';

                    itemHTML += '<li id="menu-item-' + inputCounter + '" class="mjs-nestedSortable-leaf">';
                        itemHTML += '<div class="ui-sortable-handle">';
                            itemHTML += '<div class="item-heading">';
                                itemHTML += '<span class="settings-toggle"></span>';
                                itemHTML += '<span class="menu-title">' + itemName + '</span>';
                                itemHTML += '<span class="delete-menu btn small" data-id="' + inputCounter + '">Delete</span>';
                            itemHTML += '</div>';
                            itemHTML += '<div class="item-content">';
                                itemHTML += '<input type="hidden" name="item-entry-id" value="' + itemID + '">';
                                itemHTML += '<div class="inner">';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Name') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="item-name" value="' + itemName + '">';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';                                    

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Without Link ?') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<select id="noLink-' + inputCounter + '" class="text nicetext fullwidth noLink-menu" name="noLink">';
                                                itemHTML += '<option value="0">No</option>';
                                                itemHTML += '<option value="1">Yes</option>';
                                            itemHTML += '</select>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';                                

                                    // itemHTML += '<div class="row field">';
                                    //     itemHTML += '<div class="heading">';
                                    //         itemHTML += '<label>' + Craft.t('simple-rp-menu','Display Short Content ?') + ':</label>';
                                    //     itemHTML += '</div>';
                                    //     itemHTML += '<div class="input">';
                                    //         itemHTML += '<select id="hasShortDescp-' + inputCounter + '" class="text nicetext fullwidth" name="hasShortDescp">';
                                    //             itemHTML += '<option value="0">No</option>';
                                    //             itemHTML += '<option value="1">Yes</option>';
                                    //         itemHTML += '</select>';
                                    //     itemHTML += '</div>';
                                    // itemHTML += '</div>';

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Custom Short Content') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<textarea class="text nicetext fullwidth" type="text" name="custom-short-content"></textarea>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Class') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="class" value="" />';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Class parent') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="class-parent" value="" />';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Data JSON') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<textarea class="text nicetext fullwidth" name="data-json"></textarea>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Target options') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<select id="target-' + inputCounter + '" class="text nicetext fullwidth" name="target">';
                                                itemHTML += '<option value="_self">Open in same tab</option>';
                                                itemHTML += '<option value="_blank">Open in new tab</option>';
                                            itemHTML += '</select>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            if ( itemURL ) itemHTML += '<label>' + Craft.t('simple-rp-menu','Original') + ':</label> <a href="' + itemURL + '" target="_blank">' + itemName + '</a>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                itemHTML += '</div>';
                            itemHTML += '</div>';
                        itemHTML += '</div>';
                    itemHTML += '</li>';

                    inputCounter++;
                    menuList.append(itemHTML);
                }
            });

            //reset checkes
            inputs.prop('checked', false);

            if (form.hasClass('custom-url')) {
                var customMenuTitle = $('#custom-menu-title'),
                    customMenuTitleVal = customMenuTitle.val(),
                    customMenuURL = $('#custom-menu-url'),
                    customMenuURLVal = customMenuURL.val(),
                    customMenuShortContent = $('#custom-menu-short-content'),
                    customMenuShortContentVal = customMenuShortContent.val(),
                    itemHTML = '';

                customMenuTitle.removeClass('error');
                customMenuURL.removeClass('error');

                if (customMenuTitleVal == '') {
                    if (customMenuTitleVal == '') customMenuTitle.addClass('error');
                } else {
                    itemHTML += '<li id="menu-item-' + inputCounter + '" class="mjs-nestedSortable-leaf">';
                        itemHTML += '<div class="ui-sortable-handle">';
                            itemHTML += '<div class="item-heading">';
                                itemHTML += '<span class="settings-toggle"></span>';
                                itemHTML += '<span class="menu-title">' + customMenuTitleVal + '</span>';
                                itemHTML += '<span class="delete-menu btn small" data-id="' + inputCounter + '">Delete</span>';
                            itemHTML += '</div>';
                            itemHTML += '<div class="item-content">';
                                itemHTML += '<div class="inner">';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Name') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="item-name" value="' + customMenuTitleVal + '">';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Without Link ?') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<select id="noLink-' + inputCounter + '" class="text nicetext fullwidth noLink-menu" name="noLink">';
                                                itemHTML += '<option value="0">No</option>';
                                                itemHTML += '<option value="1">Yes</option>';
                                            itemHTML += '</select>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';                                

                                    // itemHTML += '<div class="row field">';
                                    //     itemHTML += '<div class="heading">';
                                    //         itemHTML += '<label>' + Craft.t('simple-rp-menu','Display Short Content ?') + ':</label>';
                                    //     itemHTML += '</div>';
                                    //     itemHTML += '<div class="input">';
                                    //         itemHTML += '<select id="hasShortDescp-' + inputCounter + '" class="text nicetext fullwidth" name="hasShortDescp">';
                                    //             itemHTML += '<option value="0">No</option>';
                                    //             itemHTML += '<option value="1">Yes</option>';
                                    //         itemHTML += '</select>';
                                    //     itemHTML += '</div>';
                                    // itemHTML += '</div>';
                                    

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Custom Short Content') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<textarea class="text nicetext fullwidth" type="text" name="custom-short-content" >' + customMenuShortContentVal + '</textarea>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                    itemHTML += '<div class="row field" id="custom-url-wrapper">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Custom URL') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="custom-url" value="' + customMenuURLVal + '">';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Class') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="class" value="" />';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Class parent') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<input class="text nicetext fullwidth" type="text" name="class-parent" value="" />';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';
                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Data JSON') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<textarea class="text nicetext fullwidth" name="data-json"></textarea>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                    itemHTML += '<div class="row field">';
                                        itemHTML += '<div class="heading">';
                                            itemHTML += '<label>' + Craft.t('simple-rp-menu','Target options') + ':</label>';
                                        itemHTML += '</div>';
                                        itemHTML += '<div class="input">';
                                            itemHTML += '<select id="target-' + inputCounter + '" class="text nicetext fullwidth" name="target">';
                                                itemHTML += '<option value="_self">Open in same tab</option>';
                                                itemHTML += '<option value="_blank">Open in new tab</option>';
                                            itemHTML += '</select>';
                                        itemHTML += '</div>';
                                    itemHTML += '</div>';

                                itemHTML += '</div>';
                            itemHTML += '</div>';
                        itemHTML += '</div>';
                    itemHTML += '</li>';

                    inputCounter++;
                    menuList.append(itemHTML);
                }
            }
        });
    });

    //main form submit
    var mainForm = $('#menu-items form');
    mainForm.submit(function(e) {
        e.preventDefault();

        var menuListProcessed = [],
            menuListToArray = menuList.nestedSortable('toArray', {startDepthCount: 0});

        if ($.isArray(menuListToArray)) {
            for (var index in menuListToArray) {
                var menuItem = menuListToArray[index];
                if (typeof menuItem.id !== 'undefined') {
                    var menuItemEntryIDValue = '',
                        menuItemCustomURLValue= '';

                    var menuItemParentID = (typeof menuItem.parent_id !== null ) ? menuItem.parent_id : 0,
                        menuItemElement = $('#menu-item-' + menuItem.id + ' > div'),
                        menuItemID = menuItemElement.find('input[name="item-id"]'),
                        menuItemNameElement = menuItemElement.find('input[name="item-name"]'),
                        menuItemNameValue = menuItemNameElement.val(),
                        
                        menuItemEntryIDElement = menuItemElement.find('input[name="item-entry-id"]'),
                        menuItemCustomURLElement = menuItemElement.find('input[name="custom-url"]'),
                        menuItemCustomShortContentElement = menuItemElement.find('textarea[name="custom-short-content"]'),
                        menuItemCustomShortContentValue = menuItemCustomShortContentElement.val(),

                        menuItemClassElement = menuItemElement.find('input[name="class"]'),
                        menuItemClassValue = menuItemClassElement.val(),
                        menuItemClassParentElement = menuItemElement.find('input[name="class-parent"]'),
                        menuItemClassParentValue = menuItemClassParentElement.val(),
                        menuItemDataElement = menuItemElement.find('textarea[name="data-json"]'),
                        menuItemDataValue = menuItemDataElement.val();

                        if (menuItemID.length == 0) {
                            menuItemID = null;
                        } else {
                            menuItemID = menuItemID.val();
                        }
                        
                        if (menuItemCustomURLElement.length) {
                            menuItemCustomURLValue = menuItemCustomURLElement.val();
                        } else {
                            menuItemEntryIDValue = menuItemEntryIDElement.val();
                        }

                        var menuItemData = {
                        'item-id' : {
                            db:menuItemID,
                            html:menuItem.id
                        },
                        'parent-id' : menuItemParentID,
                        'name' : menuItemNameValue,
                        'entry-id' : menuItemEntryIDValue,
                        'custom-url' : menuItemCustomURLValue,
                        'custom-short-content' : menuItemCustomShortContentValue,
                        'class' : menuItemClassValue,
                        'class-parent' : menuItemClassParentValue,
                        'data-json' : menuItemDataValue,
                        'target' : $('#target-' + menuItem.id + ' :selected').val(),
                        'noLink' : $('#noLink-' + menuItem.id + ' :selected').val()
                        // 'hasShortDescp' : $('#hasShortDescp-' + menuItem.id + ' :selected').val()
                    };

                    menuListProcessed.push(menuItemData);
                }
            }

            $('#menu-items-serialized').val(JSON.stringify(menuListProcessed));
            this.submit();
        }
    });

    //delete menu from menu items list
    $('#delete-menu-with-items').on('click', function(e){
        e.preventDefault();
    });
});
function confirm_delete() {
    return confirm(Craft.t('simple-rp-menu', "Are you sure you want to delete the menu and all it's items?"));
}