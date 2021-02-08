<?php

require_once SVA_EXPORTER_INCLUDES_PATH. "authenticate.php";

function sva_exporter_export_page_html() {
    $url = get_option("sva-exporter_url");
    $key = get_option("sva-exporter_user-key");
    $secret = get_option("sva-exporter_secret");
    $token = authenticate($url."/oauth/token",$key, $secret);
    $history_data = json_decode(get_option("sva-exporter_history"),TRUE);

    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<?php

if( isset ($_GET["export"]) ) {
    $param = htmlentities($_GET["export"],ENT_QUOTES);
} else {
    $param = "";
}


if (!$param == "") {
    $posts = get_posts(array(
        'numberposts' => '99999',
        'post_type' => 'projekt',
          'category' => $param,
          'orderby' => 'modified',
          'order' => 'DESC',
    ));
} else {
    $posts = FALSE;
}


$counter = 0;
if ($posts) {

    $category = get_cat_name($param);
    $query1 = 'query{pointsOfInterest(dataProvider:\\"Wegweiser Hoher Fl\\u00e4ming\\", category: \\"'. $category .'\\"){id}}';
    // Alte Daten finden
    $response1 = wp_remote_post($url.'/graphql', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json; charset=utf-8',
        ) ,
        'body' => '{
        "query": "' . $query1 . '",
            "variables": {}
        }'
    ));


    // Alte Daten löschen
    if (!is_wp_error($response1)) {
        if (200 == wp_remote_retrieve_response_code($response1)) {
            $body = wp_remote_retrieve_body($response1);
            $data = (json_decode($body, true));
            $pois = $data["data"]["pointsOfInterest"];
            $anzahl_poi = count($pois);
            foreach ($pois as $poi) {
                $id = $poi["id"];
                // Alte Daten löschen

                $response2 = wp_remote_post($url.'/graphql', array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json; charset=utf-8',
                    ) ,
                    'body' => '{
                        "query": "mutation{destroyRecord(id:' . $id . ' recordType:\\"PointOfInterest\\"){id status statusCode}}",
                        "variables": {}
                    }',
                ));

                if (!is_wp_error($response2)) {
                    if (200 == wp_remote_retrieve_response_code($response2)) {
                    //    echo "ID " . $id . " wurde gelöscht <br>";
                    } else {
                        $error_message = wp_remote_retrieve_response_message($response2);
                    }
                } else {
                    $error_message = $response2->get_error_message();
                }
            }    // End of foreach

            update_option("sva-exporter_history", "");
            // echo "<br> Alle alten POI wurden gelöscht!<br> <br> ";

        } else {
            $error_message = wp_remote_retrieve_response_message($response1);
            echo $error_message;
        }
    } else {
        $error_message = $response1->get_error_message();
        echo $error_message;
    }
    // Alte Daten wurden gelöscht


    // Daten neu senden
    foreach ($posts as $post) {
        $other_page = $post->ID;

        if (!get_field('app', $other_page) == 1) {
            $terms = get_field('kategorie', $other_page);
            if($terms) {
                foreach ($terms as $term) {
                    if($param == $term->term_id) {
                        $query = "";
                        $query .= 'mutation{createPointOfInterest(';

                        $name = get_field('projektname', $other_page);
                        $name = str_replace('"', "'", $name);
                        $query .= 'name:\\"' . $name . '\\" ';

                        $beschreibung = get_field('projektbeschreibung', $other_page);
                        $beschreibung = str_replace(array(
                            "\r",
                            "\n"
                        ) , '', $beschreibung);
                        $beschreibung = str_replace('"', "'", $beschreibung);
                        $query .= 'description:\\"' . $beschreibung . '\\" ';

                        $query .= 'mediaContents:[';
                        $query .= '{';
                        //      	$query .= 'captionText:\\"'.get_field('projektname', $other_page).'\\" ';
                        //      	$query .= 'copyright:\\"'.get_field('projektname', $other_page).'\\" ';
                        $query .= 'contentType:\\"image\\" ';
                        $hauptbild = wp_get_attachment_image_src(get_field('bilder_hauptbild', $other_page) , 'large');
                        $query .= 'sourceUrl:{url:\\"' . $hauptbild[0] . '\\"';
                        $query .= '}}';
                        $query .= '{';
                        //      	$query .= 'captionText:\\"'.get_field('projektname', $other_page).'\\" ';
                        //      	$query .= 'copyright:\\"'.get_field('projektname', $other_page).'\\" ';
                        $query .= 'contentType:\\"image\\" ';
                        $zusatzbild_1 = wp_get_attachment_image_src(get_field('bilder_zusatzbild_1', $other_page) , 'large');
                        $query .= 'sourceUrl:{url:\\"' . $zusatzbild_1[0] . '\\"';
                        $query .= '}}';
                        $query .= '{';
                        //      	$query .= 'captionText:\\"'.get_field('projektname', $other_page).'\\" ';
                        //      	$query .= 'copyright:\\"'.get_field('projektname', $other_page).'\\" ';
                        $query .= 'contentType:\\"image\\" ';
                        $zusatzbild_2 = wp_get_attachment_image_src(get_field('bilder_zusatzbild_2', $other_page) , 'large');
                        $query .= 'sourceUrl:{url:\\"' . $zusatzbild_2[0] . '\\"';
                        $query .= '}}';
                        $query .= ']';
                        $query .= 'addresses:{';
                        $query .= 'addition:\\"' . str_replace('"', "'", get_field('adresse_ortsbezeichnung', $other_page)) . '\\" ';
                        $query .= 'street:\\"' . get_field('adresse_strasse_nr', $other_page) . '\\" ';
                        $query .= 'zip:\\"' . get_field('adresse_plz', $other_page) . '\\" ';
                        $query .= 'city:\\"' . get_field('adresse_ort', $other_page) . ' ' . get_field('adresse_ortsteil', $other_page) . '\\" ';

                        if(get_field('koordinaten_breitengrad', $other_page) && get_field('koordinaten_laengengrad', $other_page)) {
                            $query .= 'geoLocation:{';
                            $query .= 'latitude:\\"' . get_field('koordinaten_breitengrad', $other_page) . '\\" ';
                            $query .= 'longitude:\\"' . get_field('koordinaten_laengengrad', $other_page) . '\\"}}';
                        }
                        $query .= 'contact:{';
                        $query .= 'lastName:\\"' . get_field('ap_name', $other_page) . '\\" ';
                        $query .= 'phone:\\"' . get_field('ap_telefon', $other_page) . '\\" ';
                        $query .= 'email:\\"' . get_field('ap_e-mail', $other_page) . '\\"}';
                        $query .= 'webUrls:{';
                        $query .= 'url:\\"' . get_field('webseite', $other_page) . '\\" ';
                        $query .= 'description:\\"url\\"}';

                        $oeffnungszeiten = get_field('oeffnungszeiten', $other_page);
                        $oeffnungszeiten = str_replace(array(
                            "\r",
                            "\n"
                        ) , '', $oeffnungszeiten);
                        if(strlen($oeffnungszeiten) > 250 ) {
                            $oeffnungszeiten = "Für Informationen zu unseren Öffnungszeiten besuchen Sie bitte unsere Internetseite.";
                        }
                        $query .= 'openingHours:{';
                        $query .= 'description:\\"' . $oeffnungszeiten . '\\"}';

                        $query .= 'operatingCompany:{';

                        if (get_field('organisation_organisationsname', $other_page))
                        {
                            $organisationsname = get_field('organisation_organisationsname', $other_page);
                            $organisationsname = str_replace('"', "'", $organisationsname);
                            $query .= 'name:\\"' . $organisationsname . '\\" ';
                        }
                        else
                        {
                            $query .= 'name:\\"' . $name . '\\" ';
                        }
                        $query .= 'address:{';
                        $query .= 'addition:\\"' . str_replace('"', "'", get_field('organisation_ortsbezeichnung', $other_page)) . '\\" ';
                        $query .= 'street:\\"' . get_field('organisation_strasse_nr', $other_page) . '\\" ';
                        $query .= 'zip:\\"' . get_field('organisation_plz', $other_page) . '\\" ';
                        $query .= 'city:\\"' . get_field('organisation_ort', $other_page) . '\\"}';
                        $query .= 'contact:{';
                        $query .= 'lastName:\\"' . get_field('organisation_name_ap', $other_page) . '\\" ';
                        $query .= 'phone:\\"' . get_field('organisation_telefonnummer', $other_page) . '\\" ';
                        $query .= 'email:\\"' . get_field('organisation_e-mail', $other_page) . '\\"}}';
                        $query .= 'location:{';
                        $query .= 'department:\\"Belzig\\" ';
                        $query .= 'district:\\"Potsdam-Mittelmark\\"';
                        $query .= '}';
                        $query .= 'forceCreate:true ';
                        $query .= 'active:true ';
                        $query .= 'categoryName:\\"' . esc_html( $term->name ) . '\\")';
                        $query .= '{id name}}';


                        $response = wp_remote_post($url.'/graphql', array(
                            'headers' => array(
                                'Authorization' => 'Bearer ' . $token,
                                'Content-Type' => 'application/json; charset=utf-8',
                            ) ,
                            'body' => '{
                            "query": "' . $query . '",
                                "variables": {}
                        }',
                        ));


                        if (!is_wp_error($response)) {
                            if (200 == wp_remote_retrieve_response_code($response))
                            {
                                echo $name . " wurde erfolgreich (neu) importiert";
                                echo "<br>";
                            }
                            else
                            {
                                $error_message = wp_remote_retrieve_response_message($response);
                                echo "<br>";
                                echo $error_message;
                                echo ": ";
                                echo $query;
                                echo "<br><br>";
                            }
                        } else {
                            $error_message = $response->get_error_message();
                            echo $error_message;
                        }
                    }
                }
            }
        }
    }
    $history_data[$param] = date("d.m.Y H:i:s");
    $history = json_encode($history_data);
    $callback = update_option("sva-exporter_history", $history);
    $history_data = json_decode(get_option("sva-exporter_history"),TRUE);
}


        ?>

<form>
        <table style="padding-top: 30px;">
            <thead>
            <tr>
                    <th style="padding:0 30px 10px 0; text-align: left;">Kategorie</th>
                    <th style="padding:0 30px 10px 0; text-align: left;">Anzahl Einträge</th>
                    <th style="padding:0 30px 10px 0; text-align: left;">Datum der letzten Aktualisierung</th>
                    <th style="padding:0 30px 10px 0; text-align: left;">Aktion</th>
                </tr>
            </thead>
            <tbody>

        <?php

            $args = array(
                'taxonomy'               => 'category',
                'orderby'                => 'name',
                'order'                  => 'ASC',
                'hide_empty'             => false,
            );
            $the_query = new WP_Term_Query($args);

            foreach($the_query->get_terms() as $term2){
                $args2 = array(
                    'cat' => $term2->term_id,
                    'post_type' => 'projekt',
                    'post_status' => 'publish'
                );
                $the_query2 = new WP_Query( $args2 );

                ?>

                <tr>
                    <td style="padding:0 30px 10px 0"><label for="category"><?php echo $term2->name; ?></label></td>
                    <td style="padding:0 30px 10px 0"><?php echo $the_query2->found_posts;?></td>
                    <td style="padding:0 30px 10px 0">
                        <?php
                            $cat = $term2->term_id;
                            $lastExport = $history_data[$cat];
                            if($lastExport) {
                                echo "Letztes Update: ".$lastExport;
                            }
                        ?>
                    </td>
                    <td style="padding:0 30px 10px 0"><a href='admin.php?page=export&export=<?php echo $cat; ?>'>Daten jetzt senden</a></td>
                </tr>
                <?php
            } ?>
        </tbody></table></form>
        <?php require_once SVA_EXPORTER_INCLUDES_PATH. "footer.php"; ?>
    </div>
    <?php
}

?>
