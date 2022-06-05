<?php
class MngzBacklinkMonitorSetting {
    private $mngz_backlink_monitor_setting_options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'mngz_backlink_monitor_setting_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'mngz_backlink_monitor_setting_page_init' ) );
    }

    public function mngz_backlink_monitor_setting_add_plugin_page() {
       $dashboard =  add_menu_page(
            'تنظیمات بکلینک مانیتور',
            'تنظیمات بکلینک مانیتور',
            'manage_options', // capability
            'mngz_backlink_monitor', // menu_slug
            array( $this, 'mngz_backlink_monitor_setting_create_admin_page' ), // function
            'dashicons-admin-generic', // icon_url
            76 // position
        );
		
		add_action("load-{$dashboard}", "mngz_load_styles");  
    }

    public function mngz_backlink_monitor_setting_create_admin_page() {
        $this->mngz_backlink_monitor_setting_options = get_option( 'mngz_backlink_monitor_setting_option_name' ); ?>

        <div class="wrap">
            <h2>تنظیمات بکلینک مانیتور</h2>
            <p></p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'mngz_backlink_monitor_setting_option_group' );
                do_settings_sections( 'mngz-backlink-monitor-setting-admin' );
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function mngz_backlink_monitor_setting_page_init() {
        register_setting(
            'mngz_backlink_monitor_setting_option_group', // option_group
            'mngz_backlink_monitor_setting_option_name', // option_name
            array( $this, 'mngz_backlink_monitor_setting_sanitize' ) // sanitize_callback
        );

        add_settings_section(
            'mngz_backlink_monitor_setting_setting_section', // id
            'تنظیمات ', // title
            array( $this, 'mngz_backlink_monitor_setting_section_info' ), // callback
            'mngz-backlink-monitor-setting-admin' // page
        );

        add_settings_field(
            'number_check_cron', // id
            'تعداد بررسی کرون جاب', // title
            array( $this, 'number_check_cron_callback' ), // callback
            'mngz-backlink-monitor-setting-admin', // page
            'mngz_backlink_monitor_setting_setting_section' // section
        );

        add_settings_field(
            'day_check_cron', // id
            'مدت زمان بررسی لینک ها', // title
            array( $this, 'day_check_cron_callback' ), // callback
            'mngz-backlink-monitor-setting-admin', // page
            'mngz_backlink_monitor_setting_setting_section' // section
        );


        add_settings_section(
            'mngz_backlink_monitor_setting_setting_section2', // id
            'کرون جاب ', // title
            array( $this, 'mngz_backlink_monitor_setting_section_cronjob_info' ), // callback
            'mngz-backlink-monitor-setting-admin' // page
           
        );

    }

    public function mngz_backlink_monitor_setting_sanitize($input) {
        $sanitary_values = array();
        if ( isset( $input['number_check_cron'] ) ) {
            $sanitary_values['number_check_cron'] = esc_textarea( $input['number_check_cron'] );
        }
        if ( isset( $input['day_check_cron'] ) ) {
            $sanitary_values['day_check_cron'] = esc_textarea( $input['day_check_cron'] );
        }
       

        return $sanitary_values;
    }

    public function mngz_backlink_monitor_setting_section_info() {

    }
    public function mngz_backlink_monitor_setting_section_cronjob_info() {
        echo"<div>لینک زیر را در هاست از بخش کرون جاب بر روی زمان مدنظر خود تنظیم نمایید.</div>";
        echo MNGZ_BACKLINK_MONITOR_URL."cron.php";
    }

    public function number_check_cron_callback() {
        printf(
            '<input dir="ltr" class="large-text" name="mngz_backlink_monitor_setting_option_name[number_check_cron]" id="number_check_cron" value="%s" />',
            isset( $this->mngz_backlink_monitor_setting_options['number_check_cron'] ) ? esc_attr( $this->mngz_backlink_monitor_setting_options['number_check_cron']) : '5'
        );
    }
    public function day_check_cron_callback() {
        printf(
            '<input dir="ltr" class="large-text" name="mngz_backlink_monitor_setting_option_name[day_check_cron]" id="day_check_cron" value="%s" />',
            isset( $this->mngz_backlink_monitor_setting_options['day_check_cron'] ) ? esc_attr( $this->mngz_backlink_monitor_setting_options['day_check_cron']) : '30'
        );
    }
   

}
if ( is_admin() )
    $mngz_backlink_monitor_setting = new MngzBacklinkMonitorSetting();
