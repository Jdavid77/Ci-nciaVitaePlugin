<?php

function publication_setup_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Publication', 'textdomain' ),
        'menu_icon' => 'dashicons-book-alt',
        'supports' => array(
            'title'
        )
    );
    register_post_type( 'publication', $args );
}
add_action( 'init', 'publication_setup_post_type' );

function publication_add_custom_box() {
    $screens = [ 'publication', 'wporg_cpt' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'publication-box',       // Unique ID
            'Publication',      // Box title
            'custom_box_display',  // Content callback, must be of type callable
            $screen                            // Post type
        );
    }
}
add_action( 'add_meta_boxes', 'publication_add_custom_box' );

function custom_box_display( $post ) {
    ?>
    <label for="authors">Authors</label><br>
    <input readonly type="text" name="authors" id="authors" class="widefat" value="<?php echo get_post_meta($post->ID,'authors-field',true);?>"><br>

    <label for="type">Type</label><br>
    <input readonly type="text" name="type" id="type" class="widefat" value="<?php echo get_post_meta($post->ID,'type-field',true); ?>"><br>

    <label for="year">Year</label><br>
    <input readonly type="text" name="year" id="year" class="widefat" value="<?php echo get_post_meta($post->ID,'year-field',true); ?>"><br>

    <label for="researcher">Researcher</label><br>
    <input readonly type="text" name="researcher" id="researcher" class="widefat" value="<?php echo get_post_meta($post->ID,'researcher-field',true); ?>"><br>

    <label for="title">Title</label><br>
    <input readonly type="text" name="title" id="title" class="widefat" value="<?php echo get_post_meta($post->ID,'title-field',true); ?>"><br>

    <label for="link">Link</label><br>
    <input readonly type="text" name="link" id="link" class="widefat" value="<?php echo get_post_meta($post->ID,'link-field',true); ?>"><br>

    <label for="extra">Extra</label><br>
    <input readonly type="textarea" name="extra" id="extra" class="widefat" value="<?php echo get_post_meta($post->ID,'extra-field',true); ?>"><br>

    <?php
}

function custom_columns_list_publication ($columns){
    unset($columns['title']);
    unset($columns['author']);
    unset($columns['date']);

    $columns['researcher'] = 'Researcher';
    $columns['authors'] = 'Authors';
    $columns['título'] = 'Título';
    $columns['type'] = 'Type';
    $columns['year'] = 'Year';
    $columns['link'] = 'Link';
    $columns['extra'] = 'Extra';


    return $columns;
}

add_filter('manage_publication_posts_columns','custom_columns_list_publication');
add_filter('manage_publication_posts_custom_column','custom_column_data_publication',10,2);

function custom_column_data_publication ($column,$post_id){

    switch ($column){
        case 'link' :
            echo get_post_meta($post_id,'link-field',true);
            break;
        case 'extra' :
            echo get_post_meta($post_id,'extra-field',true);
            break;
        case 'título' :
            echo get_post_meta($post_id,'title-field',true);
            break;
        case 'researcher' :
            echo get_post_meta($post_id,'researcher-field',true);
            break;
        case 'year' :
            echo get_post_meta($post_id,'year-field',true);
            break;
        case 'authors' :
            echo get_post_meta($post_id, 'authors-field', true);
            break;
        case 'type' :
            echo get_post_meta($post_id,'type-field',true);
            break;
    }
}