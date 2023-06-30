<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

return array(
   'input' =>  array("name" => _w("Text field"), "icon_custom" => "input", "save_params" => array('name' => _w("New textfield"), 'code' => 'new_textfield')),
   'textarea' => array("name" => _w("Textarea"), "icon_custom" => "textarea", "save_params" => array('name' => _w("New textarea"), 'code' => 'new_textarea')),
   'number' => array("name" => _w("Number"), "icon_custom" => "number", "save_params" => array('name' => _w("New number field"), 'code' => 'new_number')),
   'email' => array("name" => _w("Email"), "icon_custom" => "email", "save_params" => array('name' => _w("New email"), 'code' => 'new_email')),
   'phone' => array("name" => _w("Phone"), "icon_custom" => "phone", "save_params" => array('name' => _w("New phone"), 'code' => 'new_phone')),
   'date' => array("name" => _w("Date field"), "icon_custom" => "date", "save_params" => array('name' => _w("New date"), 'code' => 'new_date')),
   'time' => array("name" => _w("Time"), "icon_custom" => "time", "save_params" => array('name' => _w("New time"), 'code' => 'new_time')),
   'checkbox' => array("name" => _w("Checkbox list"), "icon_custom" => "checkbox", "save_params" => array('name' => _w("New checkbox field"), 'code' => 'new_checkbox', 'options' => array(1 => array("value" => _w("First option"), "sort" => 0), 2 => array("value" => _w("Second option"), "sort" => 1), 3 => array("value" => _w("Third option"), "sort" => 1)))),
   'radio' => array("name" => _w("Radio"), "icon_custom" => "radio", "save_params" => array('name' => _w("New radio field"), 'code' => 'new_radio', 'options' => array(1 => array("value" => _w("First option"), "sort" => 0), 2 => array("value" => _w("Second option"), "sort" => 1), 3 => array("value" => _w("Third option"), "sort" => 2)))),
   'select' => array("name" => _w("Select"), "icon_custom" => "select", "save_params" => array('name' => _w("New selection field"), 'code' => 'new_select', 'options' => array(0 => array("value" => "* * * " . _w("Select option") . " * * *", "sort" => 0), 1 => array("value" => _w("First option"), "sort" => 1), 2 => array("value" => _w("Second option"), "sort" => 2), 3 => array("value" => _w("Third option"), "sort" => 3)))),
   'file' => array("name" => _w("File"), "icon_custom" => "file", "save_params" => array('name' => _w("New file"), 'code' => 'new_file')),
   'rating' => array("name" => _w("Rating"), "icon_custom" => "rating", "save_params" => array('name' => _w("New rating"), 'code' => 'new_rating')),
   'html' => array("name" => _w("HTML-text"), "icon_custom" => "html", "save_params" => array('name' => _w("New html"), 'code' => 'new_html')),
   'hidden' => array("name" => _w("Hidden"), "icon_custom" => "hidden", "save_params" => array('name' => _w("New hidden field"), 'code' => 'new_hidden')),
   'table_grid' => array("name" => _w("Table grid"), "icon_custom" => "table", "save_params" => array('name' => _w("New table grid"), 'code' => 'new_table_grid')),
   'formula' => array("name" => _w("Formula"), "icon_custom" => "formula", "save_params" => array('name' => _w("New formula"), 'code' => 'new_formula')),
   'section' => array("name" => _w("Section"), "icon_custom" => "section", "save_params" => array('name' => _w("Section"), 'code' => 'new_section')),
   'button' => array("name" => _w("Button"), "icon_custom" => "mf-button", "save_params" => array('name' => _w("Button"), 'code' => 'new_button')),
// 'page_break' => array("name" => _w("Page break"), "icon_custom" => "page-break", "save_params" => array('name' => _w("Page break"), 'code' => 'new_break')),
);
