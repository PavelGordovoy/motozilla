<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

return array(
    'title' => array(
        'value' => _wp('CRM settings'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'crm',
        'class' => 'heading margin-block bottom',
        'custom_control_wrapper' => "<div class='field custom-heading'>%s%s%s</div>",
        'skip' => 1,
    ),
    'crm_tags' => array(
        'label' => _wp('Display CRM tags before contact tabs'),
        'value' => true,
        'title' => _wp('CRM tags'),
        'control_type' => waHtmlControl::CHECKBOX,
        'app' => 'crm'
    ),
    'crm_tags_name' => array(
        'value' => _wp('CRM tags'),
        'title' => _wp('Name of link to CRM tags'),
        'control_type' => waHtmlControl::INPUT,
        'app' => 'crm'
    ),
    'title2' => array(
        'value' => _wp('Helpdesk settings'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'helpdesk',
        'class' => 'heading margin-block bottom',
        'custom_control_wrapper' => "<div class='field custom-heading'>%s%s%s</div>",
        'skip' => 1,
    ),
    'title3' => array(
        'value' => _wp('Requests'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'helpdesk',
        'class' => 'margin-block bottom',
        'custom_control_wrapper' => "<div class='field'>%s%s%s</div>",
        'skip' => 1,
    ),
    'highlight_unread' => array(
        'value' => 'e9ffcc',
        'title' => _wp('Background of unread request'),
        'control_type' => 'GetColorControl',
        'app' => 'helpdesk'
    ),
    'bg_selected' => array(
        'value' => 'ffffcc',
        'title' => _wp('Background of selected request'),
        'control_type' => 'GetColorControl',
        'app' => 'helpdesk'
    ),
    'show_tags' => array(
        'label' => _wp('Show tags in request list'),
        'value' => false,
        'title' => _wp('Tags'),
        'control_type' => waHtmlControl::CHECKBOX,
        'app' => 'helpdesk'
    ),
    'title4' => array(
        'value' => _wp('Favourite messages'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'helpdesk',
        'class' => 'margin-block bottom',
        'custom_control_wrapper' => "<div class='field'>%s%s%s</div>",
        'skip' => 1,
    ),
    'favourite_use_image' => array(
        'label' => _wp('Use image for favourite messages'),
        'value' => true,
        'title' => _wp('Favourite image'),
        'control_type' => waHtmlControl::CHECKBOX,
        'app' => 'helpdesk'
    ),
    'bg_favourite' => array(
        'value' => 'ffcc33',
        'title' => _wp('Background of favourite message'),
        'control_type' => 'GetColorControl',
        'app' => 'helpdesk'
    ),
    'title5' => array(
        'value' => _wp('Others'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'helpdesk',
        'class' => 'margin-block bottom',
        'custom_control_wrapper' => "<div class='field'>%s%s%s</div>",
        'skip' => 1,
    ),
    'ignore_ajax_errors' => array(
        'label' => _wp('Do not reset page, when something goes wrong'),
        'value' => true,
        'title' => _wp('Ajax errors'),
        'control_type' => waHtmlControl::CHECKBOX,
        'app' => 'helpdesk'
    ),
    'title6' => array(
        'value' => _wp('Spam settings'),
        'control_type' => waHtmlControl::TITLE,
        'app' => 'admin',
        'class' => 'heading margin-block bottom',
        'custom_control_wrapper' => "<div class='field custom-heading'>%s%s%s</div>",
        'skip' => 1,
    ),
    'delete_spam_contacts' => array(
        'label' => _wp('Delete spam contacts'),
        'value' => false,
        'description' => '<div class="margin-block top attention"><i class="icon16 exclamation"></i> ' . _wp('Be careful, if you enable this setting, then not only spam-requests <br>will be deleted, but also contacts that generated these requests. <br>Thus, spam-contacts with all the links to other applications will be deleted from Contacts.') . '</div>',
        'title' => _wp('Spam contacts'),
        'control_type' => waHtmlControl::CHECKBOX,
        'app' => 'admin'
    ),
    'developer_key' => array(
        'value' => '',
        'title' => _wp('Developer API key'),
        'control_type' => waHtmlControl::TEXTAREA,
        'description' => '<div class="hint">' . _wp('You can specify several values. Each on new row.') . '</div>',
        'app' => 'developer'
    ),
    'developer_id' => array(
        'value' => '',
        'title' => _wp('Developer ID'),
        'control_type' => waHtmlControl::TEXTAREA,
        'description' => '<div class="hint">' . _wp('You can specify several values. Each on new row.') . '</div>',
        'app' => 'developer'
    ),
);
