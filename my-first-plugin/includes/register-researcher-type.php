<?php

// Creating Researcher Custom Post Type
function researcher_setup_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Researcher', 'textdomain' ),
        'menu_icon' => 'dashicons-groups',
        'supports' => array(
            'title'
        )
    );
    register_post_type( 'researcher', $args );
}
add_action( 'init', 'researcher_setup_post_type' );


// Creating New Meta Boxes

function researcher_add_custom_box() {
    $screens = [ 'researcher', 'wporg_cpt' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'researcher-box',       // Unique ID
            'Researcher Info',      // Box title
            'custom_box_display_researcher',  // Content callback, must be of type callable
            $screen                            // Post type
        );
    }
}
add_action( 'add_meta_boxes', 'researcher_add_custom_box' );

function custom_box_display_researcher( $post ) {
    ?>
    <label for="researcher_name">Researcher</label><br>
    <input readonly type="text" name="researcher_name" id="researcher_name" class="postbox" value="<?php echo get_post_meta($post->ID,'research-name-field',true);?>"><br>

    <label for="ciencia_id">Ciência ID</label><br>
    <input readonly type="text" name="ciencia_id" id="ciencia_id" class="postbox" value="<?php echo get_post_meta($post->ID,'ciencia-id-field',true); ?>">

    <?php
}

// Changing the information display on the backend on the admin page

function custom_columns_list ($columns){
    unset($columns['title']);
    unset($columns['author']);
    unset($columns['date']);

    $columns['researcher'] = 'Researcher Name';
    $columns['ciencia-id'] = 'Ciencia ID';

    return $columns;
}

add_filter('manage_researcher_posts_columns','custom_columns_list');
add_filter('manage_researcher_posts_custom_column','custom_column_data',10,2);

function custom_column_data ($column,$post_id){

    switch ($column){
        case 'ciencia-id' :
            echo get_post_meta($post_id,'ciencia-id-field',true);
            break;
        case 'researcher' :
            echo get_post_meta($post_id,'research-name-field',true);
            break;
    }
}

?>