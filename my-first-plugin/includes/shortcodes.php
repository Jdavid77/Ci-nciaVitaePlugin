<?php

function register_shortcode(){
    $years = array();
    $authors = array();
    if($_GET['tipo'] && !empty($_GET['tipo'])){
        $type = $_GET['tipo'];
    }
    if($_GET['ano'] && !empty($_GET['ano'])){
        $date = $_GET['ano'];
    }
    if($_GET['artista'] && !empty($_GET['artista'])){
        $artista = $_GET['artista'];

    }
    if($_GET['order_tipo'] && !empty($_GET['order_tipo'])){
        $orderby = '';
        $tipo = $_GET['order_tipo'];
        if($tipo == 'year-field'){
            $orderby = 'meta_value_num';
        }
        elseif($tipo == 'authors-field'){
            $orderby = 'meta_value';
        }
        elseif ($tipo == 'type-field'){
            $orderby = 'meta_value';
        }
    }
    if($_GET['order_ordem'] && !empty($_GET['order_ordem'])){
        $order = $_GET['order_ordem'];
    }


    $publication = get_posts(array(
        'post_type' => 'publication',
        'numberposts' => -1
    ));

    foreach ($publication as $item) {
        if (!in_array(get_post_meta($item->ID, 'year-field', true), $years)) {
            array_push($years, get_post_meta($item->ID, 'year-field', true));
        }

        $pieces = explode(";", get_post_meta($item->ID, 'authors-field', true));
        foreach ($pieces as $piece) {
            if (!in_array($piece, $authors)) {
                array_push($authors, $piece);
            }
        }

    }
    sort($years);

    ?>
        <head>
            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            <script>
                $( function() {
                    var availableTags = <?php echo json_encode($authors) ?>;
                    $( "#autocomplete" ).autocomplete({
                        source: availableTags
                    });
                } );
            </script>

        </head>
<body>
    <div class="div-filter">
        <h4 class="label-title">Ciência Vitae Publications</h4>
        <form method="get" action="<?php the_permalink()?>">

            <label class="label-filter">Author</label>
            <input type="text" name="artista" id="autocomplete" value="<?php if (isset($_GET['artista'])) echo $_GET['artista']; ?>">

            <label class="label-filter">Type</label>
            <select name="tipo">
                <option value="">Any</option>
                <option value="Journal Article" <?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Journal Article' );?> >Journal Article</option>
                <option value="Book Book" <?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Book Book' );?> >Book</option>
                <option value="Book Chapter" <?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Book Chapter' );?> >Book Chapter</option>
                <option value="Conference Paper" <?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Conference Paper' );?> >Conference Paper</option>
                <option value="Conference Poster"<?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Conference Poster' );?> >Conference Poster</option>
                <option value="Edited Book" <?php selected( isset($_GET['tipo']) ? $_GET['tipo'] : '', 'Edited Book' );?> >Edited Book</option>
            </select>

            <label class="label-filter">Year</label>
            <select name="ano">
                <option value="">Any</option>
                <?php
                foreach ($years as $year){
                    echo '<option value="'.$year.'"' .selected( isset($_GET['ano']) ? $_GET['ano'] : '', $year ) .'>'.$year.'</option>';
                }
                ?>
            </select>

            <label class="label-filter">Order By</label>
            <select name="order_tipo">
                <option value="year-field" <?php selected( isset($_GET['order_tipo']) ? $_GET['order_tipo'] : '', 'Year' );?> >Year</option>
                <option value="authors-field" <?php selected( isset($_GET['order_tipo']) ? $_GET['order_tipo'] : '', 'Author' );?> >Author</option>
                <option value="type-field" <?php selected( isset($_GET['order_tipo']) ? $_GET['order_tipo'] : '', 'Type' );?> >Type</option>
            </select>

            <select name="order_ordem">
                <option value="DESC" <?php selected( isset($_GET['order_ordem']) ? $_GET['order_ordem'] : '', 'DESC' );?>>Descendent</option>
                <option value="ASC" <?php selected( isset($_GET['order_ordem']) ? $_GET['order_ordem'] : '', 'ASC' );?> >Ascendent</option>
            </select>

            <input type="submit" value="Filter" name="filter">
        </form>
    </div>
    <br>

        <?php


    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'publication',
        'post_status' => 'publish',
        'meta_key' => $tipo,
        'orderby' => $orderby,
        'order' => $order,
        'posts_per_page' => 10,
        'paged' => $paged,
        'meta_query' => array(
                array(
                        'key' => 'type-field',
                    'value' => $type,
                    'compare' => 'LIKE'
                ),

                array(
                        'key' => 'year-field',
                    'value' => $date,
                    'compare' => 'LIKE'
                ),
                array(
                        'key' => 'authors-field',
                    'value' => $artista,
                    'compare' => 'LIKE'
                )

        )

    );
    $publications = new WP_Query($args);


    if($publications->have_posts()){
        while($publications->have_posts()){
            $publications->the_post();
            $ID = get_the_ID();?>
            <table class="publication_list">
                <tbody>
                <tr>
                    <td>
                        <h3><?php echo get_post_meta($ID,'year-field',true); ?></h3>
                    </td>
                </tr>
                <tr>
                    <td class="publication_info">
                        <p class="publication_author"><?php echo get_post_meta($ID,'authors-field',true)?></p>
                        <p class="publication_title">
                            <?php echo get_post_meta($ID,'title-field',true);?>
                            <?php
                            if(get_post_meta($ID,'type-field',true) == 'Book Chapter') {
                                echo '<span class="book-chapter">' . get_post_meta($ID, 'type-field', true) . '</span >';
                            }
                            elseif(get_post_meta($ID,'type-field',true) == 'Journal Article'){
                                echo '<span class="journal-article">' . get_post_meta($ID, 'type-field', true) . '</span >';
                            }
                            elseif(get_post_meta($ID,'type-field',true) == 'Edited Book'){
                                echo '<span class="edited-book">' . get_post_meta($ID, 'type-field', true) . '</span >';
                            }
                            elseif(get_post_meta($ID,'type-field',true) == 'Book Book'){
                                echo '<span class="book">Book</span >';
                            }
                            elseif(get_post_meta($ID,'type-field',true) == 'Conference Paper'){
                                echo '<span class="conference-paper">' . get_post_meta($ID, 'type-field', true) . '</span >';
                            }
                            elseif(get_post_meta($ID,'type-field',true) == 'Conference Poster'){
                                echo '<span class="conference-poster">' . get_post_meta($ID, 'type-field', true) . '</span >';
                            }
                            ?>
                        </p>
                        <p class="publication_additional"><?php echo get_post_meta($ID,'extra-field',true)?></p>
                        <p class="publication_link"><a href="<?php echo get_post_meta($ID,'link-field',true)?>">Link</a></p>
                    </td>
                </tr>
                </tbody>
            </table>
</body>

        <?php
        }

        $GLOBALS['wp_query']->max_num_pages = $publications->max_num_pages;
        the_posts_pagination( array(
            'mid_size' => 1,
            'prev_text' => __( 'Back', 'green' ),
            'next_text' => __( 'Onward', 'green' ),
            'screen_reader_text' => __( 'Posts navigation' )
        ) );


    }
    else{?>
        <div style="text-align: center;border-top: black 5px;border-style: solid;border-bottom: black 5px">
            <h1 style="font-family: 'Lucida Handwriting'; font-size: 50px; font-weight: bold">Oops</h1>
            <h1 style="font-family: 'Lucida Handwriting'; font-size: 50px; font-weight: bold">No Publications Found!!</h1>
        </div>
    <?php
    }

}

add_shortcode('cienciavitae','register_shortcode');

























