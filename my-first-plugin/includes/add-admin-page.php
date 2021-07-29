<?php



function custom_form()
{

    $content = '';
    $content .= '<form method="post" action="">';
    $content .= '<label>Introduza o seu Ciência ID</label><br>';
    $content .= '<input type=text name="ID" class="input">';
    $content .= '<input type="submit" class="button" name="form_submit" value="Retrieve">';
    $content .= '</form>';

    ?>

    <p class="description">
        Bem - Vindo ao plugin Ciência Vitae.<br>
        Introduz o teu Ciência ID para obter as tuas publicações.<br>
        As publicações estarão guardadas no post type "Publications".<br>
        O teu nome também ficará guardado no post type "Researcher".<br>

        Se pretenderes atualizar as tuas publicações introduz novamente o teu Ciência ID.<br>
    </p>


<?php


    // retrieve information from API
    // and update custom post_types
    if(isset($_POST['form_submit'])){

        if(empty($_POST['ID'])){
            echo '<p class="error">Por favor introduza um ciência ID</p>';
        }
        else {

            $ID = $_POST['ID'];

            $url = "https://qa.cienciavitae.pt/api/v1.1/curriculum/$ID?lang=User%20defined"; // get entire curriculum
            //$url = "https://qa.cienciavitae.pt/api/v1.1/curriculum/$ID/output?lang=User%20defined";


            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "accept: application/json",
                "authorization: Basic TkxJTkNTX0FETUlOOjRHQDJXMzRNT0I2OQ==",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            $result = json_decode($resp, true);


            if (!curl_errno($curl)) {
                switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                    case 401:
                        echo '<p class="error">Service authentication credentials are required</p>';
                        break;
                    case 403:
                        echo '<p class="error">Forbidden operation for the given authentication credentials / Access forbidden for unpublished curriculum</p>';
                        break;
                    case 404:
                        echo '<p class="error">Invalid format for Ciência ID!!</p>';
                        break;
                    case 400:
                        echo '<p class="error">Invalid Ciência ID!!</p>';
                        break;
                    case 200:  # OK

                        // if there is already publications of this researcher
                        // i remove all of them and add them again but updated
                        if(retrieve_published_researchers($ID) == true){

                            $researcher = get_posts( array(
                                'post_type'=> 'researcher',
                                'meta_key' => 'ciencia-id-field',
                                'meta_value' => $ID,
                                'meta_compare' => '='
                               
                            ));
                            foreach ($researcher as $eachpost) {
                                wp_delete_post( $eachpost->ID, true );
                            }
                            $researcher_publications = get_posts(array(
                                'post_type' => 'publication',
                                'numberposts' => -1,
                                'meta_key' => 'researcher-field',
                                'meta_value' => $result['identifying-info']['person-info']['full-name'],
                                'meta_compare' => '='
                                )
                            );
                            foreach ($researcher_publications as $eachpost) {
                                wp_delete_post( $eachpost->ID, true );
                            }
                        }
                        //$result = json_decode($resp, true);
                        $data = $result['outputs']['output'];
                        foreach ($data as $publication) {
                            if ($publication['output-type']['code'] == 'P101') { // Journal Article
                                $extra = '';
                                if($publication['journal-article']['journal'] != NULL){
                                    $extra .= $publication['journal-article']['journal'];
                                }
                                if($publication['journal-article']['volume'] != NULL){
                                    $extra .= ", Volume: ".$publication['journal-article']['volume'];
                                }
                                if($publication['journal-article']['page-range-from'] != NULL){
                                    $extra .= ", pp: ".$publication['journal-article']['page-range-from']."-".$publication['journal-article']['page-range-to'];
                                }
                                if($publication['journal-article']['publication-location']['country']['value']){
                                    $extra .= ", ".$publication['journal-article']['publication-location']['country']['value'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['journal-article']['authors']['citation'],
                                        'type-field' => 'Journal Article',
                                        'year-field' => $publication['journal-article']['publication-date']['year'],
                                        'link-field' => $publication['journal-article']['url'],
                                        'title-field' => $publication['journal-article']['article-title'],
                                        'extra-field' => $extra
                                    )
                                );
                                wp_insert_post($my_post);
                            }
                            if ($publication['output-type']['code'] == 'P122') { // Conference Paper
                                $extra = '';
                                if($publication['conference-paper']['conference-name'] != NULL){
                                    $extra .= $publication['conference-paper']['conference-name'];
                                }
                                if($publication['conference-paper']['conference-location'] != NULL){
                                    $extra .= ', '.$publication['conference-paper']['conference-location']['value'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['conference-paper']['authors']['citation'],
                                        'type-field' => 'Conference Paper',
                                        'year-field' => $publication['conference-paper']['conference-date']['year'],
                                        'title-field' => $publication['conference-paper']['paper-title'],
                                        'extra-field' => $extra,
                                        'link-field' => NULL
                                    ));
                                wp_insert_post($my_post);
                            }

                            if ($publication['output-type']['code'] == 'P103') { // Book
                                $extra = '';
                                if($publication['book']['volume'] != NULL){
                                    $extra .= 'Volume :'.$publication['book']['volume'];
                                }
                                if($publication['book']['edition'] != NULL){
                                    $extra .= ', Edition: '.$publication['book']['edition'];
                                }
                                if($publication['book']['number-of-pages'] != NULL){
                                    $extra .= ', '.$publication['book']['number-of-pages'];
                                }
                                if($publication['book']['publisher'] != NULL){
                                    $extra .= ', '.$publication['book']['publisher'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['book']['authors']['citation'],
                                        'type-field' => 'Book Book',
                                        'year-field' => $publication['book']['publication-year'],
                                        'title-field' => $publication['book']['title'],
                                        'extra-field' => $extra,
                                        'link-field' => $publication['book']['url']
                                    ));
                                wp_insert_post($my_post);
                            }
                            if ($publication['output-type']['code'] == 'P105') { // Book Chapter
                                $extra = '';
                                if($publication['book-chapter']['book-title'] != NULL){
                                    $extra .= $publication['book-chapter']['book-title'];
                                }
                                if($publication['book-chapter']['book-volume'] != NULL){
                                    $extra .= ', Volume: '.$publication['book-chapter']['book-volume'];
                                }
                                if($publication['book-chapter']['book-edition'] != NULL){
                                    $extra .= ', Edition: '.$publication['book-chapter']['book-edition'];
                                }
                                if($publication['book-chapter']['book-publisher'] != NULL){
                                    $extra .= ', '.$publication['book-chapter']['book-publisher'];
                                }
                                if($publication['book-chapter']['publication-location']['country']['value']){
                                    $extra .= ', '.$publication['book-chapter']['publication-location']['country']['value'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['book-chapter']['authors']['citation'],
                                        'type-field' => 'Book Chapter',
                                        'year-field' => $publication['book-chapter']['publication-year'],
                                        'title-field' => $publication['book-chapter']['chapter-title'],
                                        'extra-field' => $extra,
                                        'link-field' => $publication['book-chapter']['url']

                                    ));
                                wp_insert_post($my_post);
                            }

                            if ($publication['output-type']['code'] == 'P104') { // Edited Book
                                $extra = '';
                                if($publication['edited-book']['volume'] != NULL){
                                    $extra .= 'Volume: '.$publication['edited-book']['volume'];
                                }
                                if($publication['edited-book']['edition'] != NULL){
                                    $extra .= ', Edition:'.$publication['edited-book']['edition'];
                                }
                                if($publication['edited-book']['number-of-pages'] != NULL){
                                    $extra .= ', pp:'.$publication['edited-book']['number-of-pages'];
                                }
                                if($publication['edited-book']['publication-location']['country']['value'] != NULL){
                                    $extra .= ', '.$publication['edited-book']['publication-location']['country']['value'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['edited-book']['authors']['citation'],
                                        'type-field' => 'Edited Book',
                                        'year-field' => $publication['edited-book']['publication-year'],
                                        'title-field' => $publication['edited-book']['title'],
                                        'link-field' => $publication['edited-book']['url'],
                                        'extra-field' => $extra
                                    ));
                                wp_insert_post($my_post);
                            }
                            if ($publication['output-type']['code'] == 'P124') { // Conference Poster
                                $extra = '';
                                if($publication['conference-poster']['conference-name']){
                                    $extra .= $publication['conference-poster']['conference-name'];
                                }
                                $my_post = array(
                                    //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                    'post_status' => 'publish',
                                    'post_type' => 'publication',
                                    'meta_input' => array(
                                        'researcher-field' => $result['identifying-info']['person-info']['full-name'],
                                        'authors-field' => $publication['conference-poster']['authors']['citation'],
                                        'type-field' => 'Conference Poster',
                                        'year-field' => $publication['conference-poster']['conference-date']['year'],
                                        'title-field' => $publication['conference-poster']['title'],
                                        'extra-field' => $extra,
                                        'link-field' => NULL
                                    ));
                                wp_insert_post($my_post);
                            }

                        }
                            $my_post = array(
                                //'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
                                'post_status' => 'publish',
                                'post_type' => 'researcher',
                                'meta_input' => array(
                                    'research-name-field' => $result['identifying-info']['person-info']['full-name'],
                                    'ciencia-id-field' => $result['identifying-info']['author-identifiers']['author-identifier']['0']['identifier']
                                )
                            );

                            wp_insert_post($my_post);


                        echo '<p class="success">Publicações adicionadas/atualizadas!!</p>';
                        break;
                    default:
                        echo 'Unexpected HTTP code: ', $http_code, "\n";
                }
            }

            curl_close($curl);
        }
    }

    echo $content;

}

function retrieve_published_researchers($id){

   $post_exists = (new WP_Query(['post_type' => 'researcher','post_status' => 'published','meta_key' => 'ciencia-id-field','meta_value' => $id,'meta_compare' => '=']))->found_posts > 0;

   if($post_exists){
       echo 'Tem';
       return true;
   }
   echo 'Não tem';
   return false;
}

/**
 * Register a custom menu page.
 */
function wpdocs_register_my_custom_menu_page() {
    add_menu_page(
        __( 'Ciência Vitae', 'textdomain' ),
        'CiênciaVitae',
        'manage_options',
        'cienciavitae-plugin.php',
        'custom_form',
        'dashicons-welcome-learn-more',
        85
    );
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );

?>