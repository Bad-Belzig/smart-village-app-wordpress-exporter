<?php

require_once SVA_EXPORTER_ADMIN_PATH."sva_exporter_options.php";
require_once SVA_EXPORTER_ADMIN_PATH."sva_exporter_delete.php";
require_once SVA_EXPORTER_ADMIN_PATH."sva_exporter_export.php";


add_action('admin_menu', 'svae_admin_menue');

function svae_admin_menue() {

    add_menu_page(
        "SVA Exporter",
        "SVA Exporter",
        "manage_options",
        "sva-exporter",
        "sva_exporter_page_html",
        "dashicons-smartphone",
        -100
    );

    add_submenu_page(
        "sva-exporter",
        "SVA Exporter: Options",
        "Options",
        "manage_options",
        "sva-exporter",
        "sva_exporter_options_page_html"
    );

    add_submenu_page(
        "sva-exporter",
        "SVA Exporter: Delete",
        "Delete",
        "manage_options",
        "delete",
        "sva_exporter_delete_page_html"
    );

    add_submenu_page(
        "sva-exporter",
        "SVA Exporter: Export",
        "Export",
        "manage_options",
        "export",
        "sva_exporter_export_page_html"
    );

}

function sva_exporter_page_html() {
}


?>