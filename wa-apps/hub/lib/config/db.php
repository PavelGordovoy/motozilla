<?php
return array(
    'hub_author' => array(
        'hub_id' => array('int', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'topics_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'comments_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'answers_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_up' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_down' => array('int', 11, 'null' => 0, 'default' => '0'),
        'rate' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'hub_id'),
        ),
    ),
    'hub_category' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'hub_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'description' => array('varchar', 255),
        'type' => array('int', 1, 'null' => 0, 'default' => '0'),
        'conditions' => array('text'),
        'url' => array('varchar', 255, 'null' => 0),
        'topics_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        'sorting' => array('varchar', 32),
        'enable_sorting' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'update_datetime' => array('datetime'),
        'logo' => array('varchar', 255, 'null' => 0, 'default' => ''),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'hub_comment' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'hub_id' => array('int', 11, 'null' => 0),
        'topic_id' => array('int', 11, 'null' => 0),
        'left_key' => array('int', 11, 'null' => 0),
        'right_key' => array('int', 11, 'null' => 0),
        'depth' => array('int', 11, 'null' => 0),
        'parent_id' => array('int', 11),
        'contact_id' => array('int', 11, 'null' => 0),
        'datetime' => array('datetime', 'null' => 0),
        'status' => array('enum', "'approved','deleted'", 'null' => 0, 'default' => 'approved'),
        'text' => array('text', 'null' => 0),
        'ip' => array('int', 11),
        'votes_up' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_down' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_sum' => array('int', 11, 'null' => 0, 'default' => '0'),
        'solution' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'votes_sum' => 'votes_sum',
            'hub_contact' => array('hub_id', 'contact_id'),
        ),
    ),
    'hub_filter' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'conditions' => array('text', 'null' => 0),
        'topics_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'icon' => array('varchar', 255, 'null' => 0),
        'update_datetime' => array('datetime', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
            'contact_id' => 'contact_id',
        ),
    ),
    'hub_following' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'topic_id' => array('int', 11, 'null' => 0),
        'datetime' => array('datetime', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'topic_id'),
            'topic_id' => 'topic_id',
        ),
    ),
    'hub_hub' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        'status' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'topics_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'hub_hub_params' => array(
        'hub_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 64, 'null' => 0),
        'value' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('hub_id', 'name'),
        ),
    ),
    'hub_hub_types' => array(
        'hub_id' => array('int', 11, 'null' => 0),
        'type_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('hub_id', 'type_id'),
        ),
    ),
    'hub_page' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'parent_id' => array('int', 11),
        'domain' => array('varchar', 255),
        'route' => array('varchar', 255),
        'name' => array('varchar', 255, 'null' => 0),
        'title' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'url' => array('varchar', 255),
        'full_url' => array('varchar', 255),
        'content' => array('mediumtext', 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime', 'null' => 0),
        'create_contact_id' => array('int', 11, 'null' => 0),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        'status' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'hub_page_params' => array(
        'page_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'value' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('page_id', 'name'),
        ),
    ),
    'hub_staff' => array(
        'hub_id' => array('int', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'badge' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'badge_color' => array('varchar', 7),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('hub_id', 'contact_id'),
        ),
    ),
    'hub_tag' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'hub_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'count' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'hub_topic' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'hub_id' => array('int', 11, 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime', 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'title' => array('varchar', 255, 'null' => 0),
        'content' => array('longtext', 'null' => 0),
        'url' => array('varchar', 255, 'null' => 0),
        'votes_up' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_down' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'votes_sum' => array('int', 11, 'null' => 0, 'default' => '0'),
        'comments_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'badge' => array('enum', "'archived','answered','pending','accepted','confirmed','inprogress','complete','fixed','rejected'"),
        'type_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'priority' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'followers_count' => array('int', 11, 'null' => 0, 'default' => '0'),
        'status' => array('tinyint', 1, 'null' => 0, 'default' => '1'),
        'meta_title' => array('varchar', 255),
        'meta_keywords' => array('text'),
        'meta_description' => array('text'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'votes_sum' => 'votes_sum',
            'hub_contact' => array('hub_id', 'contact_id'),
            'title_content' => array('title', 'content', 'fulltext' => 1),
        ),
    ),
    'hub_topic_categories' => array(
        'topic_id' => array('int', 11, 'null' => 0),
        'category_id' => array('int', 11, 'null' => 0),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('topic_id', 'category_id'),
        ),
    ),
    'hub_topic_params' => array(
        'topic_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 64, 'null' => 0),
        'value' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('topic_id', 'name'),
            'name' => 'name',
        ),
    ),
    'hub_topic_tags' => array(
        'topic_id' => array('int', 11, 'null' => 0),
        'tag_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('topic_id', 'tag_id'),
        ),
    ),
    'hub_type' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'glyph' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'type' => array('varchar', 255, 'null' => 0),
        'settings' => array('text'),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'hub_vote' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'topic_id' => array('int', 11, 'null' => 0),
        'comment_id' => array('int', 11),
        'contact_id' => array('int', 11, 'null' => 0),
        'vote' => array('int', 11, 'null' => 0),
        'datetime' => array('datetime', 'null' => 0),
        'ip' => array('int', 11),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
