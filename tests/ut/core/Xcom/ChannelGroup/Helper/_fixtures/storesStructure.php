<?php
/**
 * Get structure of websites, store groups and store views
 *
 * @return array
 */
return array(
    'websites' => array (
        0 => array (
            'id'            => '0',
            'website_id'    => '0',
            'code'          => 'admin',
            'name'          => 'Admin',
            'sort_order'    => '0',
            'default_group_id' => '0',
            'is_default'    => '0',
        ),
        1 => array (
            'id'            => '1',
            'website_id'    => '1',
            'code'          => 'base',
            'name'          => 'Main Website',
            'sort_order'    => '0',
            'default_group_id' => '1',
            'is_default'    => '1',
        ),
    ),
    'groups' => array (
        0 => array (
            'id'            => '0',
            'group_id'      => '0',
            'website_id'    => '0',
            'name'          => 'Default',
            'root_category_id' => '0',
            'default_store_id' => '0',
        ),
        2 => array (
            'id'            => '2',
            'group_id'      => '2',
            'website_id'    => '1',
            'name'          => 'Main some store',
            'root_category_id' => '223',
            'default_store_id' => '4',
        ),
        1 => array (
            'id'            => '1',
            'group_id'      => '1',
            'website_id'    => '1',
            'name'          => 'Main Website Store',
            'root_category_id' => '2',
            'default_store_id' => '1',
        ),
    ),
    'stores' => array (
        0 => array (
            'id'            => '0',
            'store_id'      => '0',
            'code'          => 'admin',
            'website_id'    => '0',
            'group_id'      => '0',
            'name'          => 'Admin',
            'sort_order'    => '0',
            'is_active'     => '1',
        ),
        1 => array (
            'id'            => '1',
            'store_id'      => '1',
            'code'          => 'en',
            'website_id'    => '1',
            'group_id'      => '1',
            'name'          => 'en-Default Store View',
            'sort_order'    => '1',
            'is_active'     => '1',
        ),
        2 => array (
            'id'            => '2',
            'store_id'      => '2',
            'code'          => 'fr',
            'website_id'    => '1',
            'group_id'      => '1',
            'name'          => 'FR',
            'sort_order'    => '2',
            'is_active'     => '1',
        ),
        3 => array (
            'id'            => '3',
            'store_id'      => '3',
            'code'          => 'de',
            'website_id'    => '1',
            'group_id'      => '1',
            'name'          => 'DE',
            'sort_order'    => '3',
            'is_active'     => '1',
        ),
        100 => array (
            'id'            => '100',
            'store_id'      => '100',
            'code'          => 'some_fr',
            'website_id'    => '1',
            'group_id'      => '2',
            'name'          => 'some FR',
            'sort_order'    => '10',
            'is_active'     => '1',
        ),
        4 => array (
            'id'            => '4',
            'store_id'      => '4',
            'code'          => 'some_en',
            'website_id'    => '1',
            'group_id'      => '2',
            'name'          => 'some EN',
            'sort_order'    => '20',
            'is_active'     => '1',
        ),
    )
);
