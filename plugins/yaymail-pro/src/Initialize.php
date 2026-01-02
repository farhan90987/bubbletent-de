<?php
namespace YayMail;

use YayMail\Elements\ElementsLoader;
use YayMail\Emails\EmailsLoader;
use YayMail\Engine\ActDeact;
use YayMail\Engine\Backend\SettingsPage;
use YayMail\Engine\RestAPI;
use YayMail\PostTypes\TemplatePostType;
use YayMail\Shortcodes\ShortcodesLoader;
use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\IntegrationsLoader;
use YayMail\TemplatePatterns\PatternsLoader;
use YayMail\TemplatePatterns\SectionTemplatesLoader;
use YayMail\PreviewEmail\PreviewEmailsLoader;
use YayMail\Notices\NoticeMain;
use YayMail\License\License;

/**
 * YayMail Plugin Initializer
 *
 * @method static Initialize get_instance()
 */
class Initialize {

    use SingletonTrait;

    /**
     * The Constructor that load the engine classes
     */
    protected function __construct() {
        add_action( 'init', [ $this, 'yaymail_init' ] );
        I18n::get_instance();
    }

    public static function yaymail_init() {
        require_once YAYMAIL_PLUGIN_PATH . 'src/Functions.php';

        do_action( 'yaymail_init_start' );

        $license = new License( 'yaymail' );
        if ( ! $license->is_active() ) {
            return;
        }

        ActDeact::get_instance();

        WooHandler::get_instance();
        /**
         * Core Integrations
         */
        IntegrationsLoader::get_instance();

        /**
         * Preview Email loader
         */

        PreviewEmailsLoader::get_instance();

        /**
         * Supported templates
         */
        SupportedPlugins::get_instance();

        /**
         * Core core filters
         */
        EmailsLoader::get_instance();
        ElementsLoader::get_instance();
        ShortcodesLoader::get_instance();

        SectionTemplatesLoader::get_instance();
        PatternsLoader::get_instance();

        /**
         * Initialize rest api
         */
        RestAPI::get_instance();

        /**
         * Initialize pages
         */
        SettingsPage::get_instance();

        TemplatePostType::get_instance();
        Ajax::get_instance();

        /**
         * Notices
         */
        NoticeMain::get_instance();

        do_action( 'yaymail_loaded' );
    }
}
