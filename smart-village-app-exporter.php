<?php

/*
Plugin Name: Smart Village App Exporter
Plugin URI:  https://smart-village.app
Description: Exportiert Daten zur Smart Village App
Version:     1.1
Author:      Philipp Wiliumzig, Smart Village Solutions
Author URI:  https://smart-village.solutions
License:     GPL3
*/
$imagepath = plugins_url("assets/images/",__FILE__);
define("SVA_EXPORTER_IMAGES_PATH", $imagepath);
define("SVA_EXPORTER_CSS_PATH", __DIR__ . "/assets/css/");
define("SVA_EXPORTER_JS_PATH", __DIR__ . "/assets/js/");
define("SVA_EXPORTER_INCLUDES_PATH", __DIR__ . "/assets/includes/");
define("SVA_EXPORTER_ADMIN_PATH", __DIR__ . "/admin/");

require_once SVA_EXPORTER_ADMIN_PATH."admin.php";

// add_action("save_post", "sva_exporter_save_post");

// function sva_exporter_save_post() {
    // Diese Funktion würde bei jeder Speicherung eines Post ausgeführt
    // ToDo: Export nach jeder Speicheruzng eines Projekts ausführen

//}
?>