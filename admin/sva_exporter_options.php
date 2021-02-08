<?php


function sva_exporter_settings_init() {
    register_setting('sva_exporter_options', 'sva-exporter_url');
    register_setting('sva_exporter_options', 'sva-exporter_user-key');
    register_setting('sva_exporter_options', 'sva-exporter_secret');
    register_setting('sva_exporter_options', 'sva-exporter_history');

    add_settings_section(
        'sva_exporter_options',
        '',
        'svae_settings_section_callback',
        'sva-exporter'
    );

    add_settings_field(
        'sva-exporter_url',
        'Server-URL',
        'sva_exporter_url_callback',
        'sva-exporter',
        'sva_exporter_options'
    );


    add_settings_field(
        'sva-exporter-user-key',
        'Application Key',
        'sva_exporter_user_key_callback',
        'sva-exporter',
        'sva_exporter_options'
    );

    add_settings_field(
        'sva-exporter_secret',
        'Secret',
        'sva_exporter_secret_callback',
        'sva-exporter',
        'sva_exporter_options'
    );

    // add_settings_field(
    //     'sva-exporter_history',
    //     'Secret',
    //     'sva_exporter_history_callback',
    //     'sva-exporter',
    //     'sva_exporter_options'
    // );
}

/**
 * register wporg_settings_init to the admin_init action hook
 */
add_action('admin_init', 'sva_exporter_settings_init');

function sva_exporter_url_callback() {
    ?>
        <input type="text" id="sva-exporter_url" name="sva-exporter_url" value="<?php echo get_option("sva-exporter_url")?>" style="width:500px;">
    <?php

}

function sva_exporter_user_key_callback() {
    ?>
        <input type="text" id="sva-exporter_user-key" name="sva-exporter_user-key" value="<?php echo get_option("sva-exporter_user-key")?>" style="width:500px;">
    <?php
}

function sva_exporter_secret_callback() {
    ?>
        <input type="text" id="sva-exporter_secret" name="sva-exporter_secret" value="*****" style="width:500px;">
    <?php
}

function sva_exporter_history_callback() {
    ?>
        <textarea rows="10" cols="50" id="sva-exporter_history" name="sva-exporter_history" style="width:500px;"><?php echo get_option("sva-exporter_history")?></textarea>
    <?php
}

function svae_settings_section_callback() {
    ?>
        <p>Bitte trage hier Deine Zugangsdaten zum Smart Village App Datenserver ein.</p>
    <?php
}

function sva_exporter_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields("sva_exporter_options");
            do_settings_sections("sva-exporter");
            submit_button();
            ?>
        </form>
        <?php require_once SVA_EXPORTER_INCLUDES_PATH. "footer.php"; ?>
    </div>
    <?php
}


?>