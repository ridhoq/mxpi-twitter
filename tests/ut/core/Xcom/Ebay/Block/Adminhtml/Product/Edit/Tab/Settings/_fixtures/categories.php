<?php
/**
 * Return recommended categories list
 */
$cnt = 0;
$items[] = array(
    'id'             => ++$cnt,
    'path'           => $cnt,
    'parent_id'      => $cnt,
    'name'           => 'Name Root ' . $cnt,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$items[] = array(
    'id'             => ++$cnt,
    'path'           => $cnt,
    'parent_id'      => $cnt,
    'name'           => 'Name Root ' . $cnt,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$items[] = array(
    'id'             => ++$cnt,
    'path'           => $cnt,
    'parent_id'      => $cnt,
    'name'           => 'Name Root ' . $cnt,
    'leaf_category'  => 0,
    'children_count' => 1,
);
$items[] = array(
    'id'             => ++$cnt,
    'path'           => $cnt - 1 . '/' . $cnt,
    'parent_id'      => $cnt - 1,
    'name'           => 'Name ' . $cnt,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$items[] = array(
    'id'             => ++$cnt,
    'path'           => $cnt,
    'parent_id'      => $cnt,
    'name'           => 'Name Root ' . $cnt,
    'leaf_category'  => 1,
    'children_count' => 0,
);

$id = 20;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/19/' . $id,
    'parent_id'      => 19,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);

$id = 19;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/' . $id,
    'parent_id'      => 14,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 0,
    'children_count' => 1,
);
$id = 18;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/' . $id,
    'parent_id'      => 14,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$id = 21;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/' . $id,
    'parent_id'      => 14,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$id = 22;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/17/' . $id,
    'parent_id'      => 17,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$id = 23;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/17/' . $id,
    'parent_id'      => 17,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$id = 17;
$items[] = array(
    'id'             => $id,
    'path'           => '12/14/' . $id,
    'parent_id'      => 14,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 0,
    'children_count' => 3,
);
$id = 14;
$items[] = array(
    'id'             => $id,
    'path'           => '12/' . $id,
    'parent_id'      => 12,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 0,
    'children_count' => 3,
);
$id = 15;
$items[] = array(
    'id'             => $id,
    'path'           => '12/' . $id,
    'parent_id'      => 12,
    'name'           => 'Name ' . $id,
    'leaf_category'  => 1,
    'children_count' => 0,
);
$id = 12;
$items[] = array(
    'id'             => $id,
    'path'           => $id,
    'parent_id'      => 12,
    'name'           => 'Name Root ' . $id,
    'leaf_category'  => 0,
    'children_count' => 2,
);
$expected = array(
    array(
        'id' => 1,
        'text' => 'Name Root 1',
        'leaf' => true,
        'cls' => 'file',
    ),
    array(
        'id' => 2,
        'text' => 'Name Root 2',
        'leaf' => true,
        'cls' => 'file',
    ),
    array(
        'id' => 3,
        'text' => 'Name Root 3',
        'disabled' => true,
        'cls' => 'folder',
        'children' =>
        array(
            array(
                'id' => 4,
                'text' => 'Name 4',
                'leaf' => true,
                'cls' => 'file',
            ),
        ),
    ),
    array(
        'id' => 5,
        'text' => 'Name Root 5',
        'leaf' => true,
        'cls' => 'file',
    ),
    array(
        'id' => 12,
        'text' => 'Name Root 12',
        'disabled' => true,
        'cls' => 'folder',
        'children' => array(
            array(
                'id' => 14,
                'text' => 'Name 14',
                'disabled' => true,
                'cls' => 'folder',
                'children' => array(
                    array(
                        'id' => 19,
                        'text' => 'Name 19',
                        'disabled' => true,
                        'cls' => 'folder',
                        'children' => array(
                            array(
                                'id' => 20,
                                'text' => 'Name 20',
                                'leaf' => true,
                                'cls' => 'file',
                            ),
                        ),
                    ),
                    array(
                        'id' => 18,
                        'text' => 'Name 18',
                        'leaf' => true,
                        'cls' => 'file',
                    ),
                    array(
                        'id' => 21,
                        'text' => 'Name 21',
                        'leaf' => true,
                        'cls' => 'file',
                    ),
                    array(
                        'id' => 17,
                        'text' => 'Name 17',
                        'disabled' => true,
                        'cls' => 'folder',
                        'children' => array(
                            array(
                                'id' => 22,
                                'text' => 'Name 22',
                                'leaf' => true,
                                'cls' => 'file',
                            ),
                            array(
                                'id' => 23,
                                'text' => 'Name 23',
                                'checked' => true,
                                'leaf' => true,
                                'cls' => 'file',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id' => 15,
                'text' => 'Name 15',
                'leaf' => true,
                'cls' => 'file',
            ),
        ),
    ),
);

return array(
    'items' => $items,
    'selected_category_id' => 23,
    'expected' => $expected);
