<?php
require_once SVA_EXPORTER_INCLUDES_PATH. "authenticate.php";



function sva_exporter_delete_page_html() {
    $url = get_option("sva-exporter_url");
    $key = get_option("sva-exporter_user-key");
    $secret = get_option("sva-exporter_secret");
    $token = authenticate($url."/oauth/token",$key, $secret);

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php
        // Alte Daten finden
        $response1 = wp_remote_post($url.'/graphql', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json; charset=utf-8',
            ),
            'body' => '{
                "query": "query{pointsOfInterest(dataProvider:\\"Wegweiser Hoher Fl\\u00e4ming\\"){id}}",
                "variables": {}
            }',
        ));

        if (!is_wp_error($response1)) {
            if (200 == wp_remote_retrieve_response_code($response1)) {
                $body = wp_remote_retrieve_body($response1);
                $data = (json_decode($body, true));
                $pois = $data["data"]["pointsOfInterest"];
                $anzahl_poi = count($pois);
                if( isset ($_GET["delete"]) ) {
                    $param = htmlentities($_GET["delete"],ENT_QUOTES);
                } else {
                    $param = "";
                }
                if($param == "true") {
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
                                echo "ID " . $id . " wurde gelöscht <br>";
                            } else {
                                $error_message = wp_remote_retrieve_response_message($response2);
                            }
                        } else {
                            $error_message = $response2->get_error_message();
                        }
                    }    // End of foreach

                    update_option("sva-exporter_history", "");
                    echo "<br> Alle POI wurden gelöscht!";

                } else {

                    echo "<p>Es wurden ".$anzahl_poi." POIs gefunden. Möchten Sie diese nun löschen?</p>";
                    ?>
                    <a href="admin.php?page=delete&delete=true">Ja, löschen!</a>

                    <?php
                }
            } else {
                $error_message = wp_remote_retrieve_response_message($response1);
            }
        } else {
            $error_message = $response1->get_error_message();
        } ?>

    <?php require_once SVA_EXPORTER_INCLUDES_PATH. "footer.php"; ?>

    </div>
    <?php
}
?>