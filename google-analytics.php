<?php
/*
Plugin Name: Google Analytics
Plugin URI: http://www.semiologic.com/software/google-analytics/
Description: Adds <a href="http://analytics.google.com">Google analytics</a> to your blog, with various advanced tracking features enabled.
Version: 6.1
Author: Denis de Bernardy & Mike Koepke
Author URI: http://www.semiologic.com
Text Domain: google-analytics
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.
**/


if ( !defined('sem_google_analytics_debug') )
	define('sem_google_analytics_debug', false);

if ( !defined('GA_DOMAIN') )
	define('GA_DOMAIN', false);


/**
 * google_analytics
 *
 * @package Google Analytics
 **/

class google_analytics {

	protected $use_universal = false;

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}


	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain )
	{
		load_plugin_textdomain(
			$domain,
			FALSE,
			dirname(plugin_basename(__FILE__)) . '/lang'
		);
	}

	/**
	 * Constructor.
	 *
	 *
	 */
	public function __construct() {
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );
		$this->load_language( 'google-analytics' );

		add_action( 'plugins_loaded', array ( $this, 'init' ) );

		$options = google_analytics::get_options();
		if ( isset( $options['universal_analytics']) && (bool) $options['universal_analytics'] )
			$this->use_universal = true;

    } # google_analytics()


	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		if ( !is_admin() ) {
		    add_action('wp_enqueue_scripts', array($this, 'header_scripts'));
		    add_action('wp_footer', array($this, 'footer_scripts'), 20);
		    add_action('wp_footer', array($this, 'track_page'), 1000); // after script manager
		    add_action('mediacaster_audio', array($this, 'track_media'));
		    add_action('mediacaster_video', array($this, 'track_media'));
	    } else {
		    add_action('admin_menu', array($this, 'admin_menu'));
			add_action('load-settings_page_google-analytics', array($this, 'google_analytics_admin'));
	    }
	}

	/**
	* google_analytics_admin()
	*
	* @return void
	**/
	function google_analytics_admin() {
		include_once $this->plugin_path . '/google-analytics-admin.php';
	}

    /**
	 * domain name parts to be tracked
	 *
	 * @return array domain name parts
	 **/

	function getDomainParts() {
		$domain = get_option('home');
		$domain = parse_url($domain);
        if ($domain == false)
            return array();
        elseif (is_array($domain)) {
            if (isset($domain['host']))
                $domain = $domain['host'];
            else
                return array();
        }
		$domain = preg_replace("/^www\./i", '', $domain);
		$domain = explode('.', $domain);
		if ( count($domain) > 2 ) {
			$_domain = $domain;
			$domain = array();
			do {
				$tail = array_pop($_domain);
				array_unshift($domain, $tail);
				if ( strlen($tail) > 4 )
					break;
			} while ( $_domain );
		}
		
		return $domain;
	}
	
	/**
	 * header_scripts()
	 *
	 * @return void
	 **/

	function header_scripts() {
        $options = google_analytics::get_options();
        $uacct = $options['uacct'];
        $useSubdomains = $options['subdomains'];
		
		$domainParts = self::getDomainParts();
        $domain = implode( '.', $domainParts);
		$domainRegex = '[^/]+://[^/]*' . implode('\\.', array_map('addslashes', $domainParts)) . '(/|$)';
		$ga_domain = '';

		// if doing development
		if ( $domain == 'localhost')
			$domain = 'none';
		else {
			if ( $useSubdomains && !$this->use_universal ) {
				$ga_domain = "\n_gaq.push(['_setDomainName', '" . $domain . "']);";
				$ga_domain .= "\n_gaq.push(['_setAllowLinker', true]);";
			}
			else
				$domain = 'auto';
		}

		$ga_displayTracking = '';
		if ( $options['displayTracking'] && $this->use_universal ) {
			$ga_displayTracking = "ga('require', 'displayfeatures');";
		}

		if ( $this->use_universal ) {
			$gacode = <<<EOS
<script type="text/javascript">
	window.google_analytics_uacct = "$uacct";
	window.google_analytics_regexp = new RegExp("$domainRegex", 'i');
</script>
<script type="text/javascript">
   (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
   (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
   m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
   })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

   ga('create', '$uacct', '$domain');
   $ga_displayTracking
   ga('send', 'pageview');
 </script>

EOS;
		}
		else {
		$gacode = <<<EOS
<script type="text/javascript">
	window.google_analytics_uacct = "$uacct";
	window.google_analytics_regexp = new RegExp("$domainRegex", 'i');
</script>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '$uacct']);$ga_domain
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>

EOS;
		}

		echo $gacode;

		if ( !sem_google_analytics_debug && ( !$uacct || current_user_can('publish_posts') || current_user_can('publish_pages') ) )
			return;

//		$analytics_js = ( WP_DEBUG ? 'scripts.min.js' : 'scripts.js' );
		$analytics_js = 'scripts.js';
		if ( $this->use_universal )
			$analytics_js = 'ua-' . $analytics_js;
		wp_enqueue_script('google_analytics', plugins_url( '/js/' . $analytics_js, __FILE__), array('jquery'), '20140618', true);

/*
		wp_localize_script('google_analytics', 'google_analyticsL10n', array(
			'ad_event' => __('Ad Unit', 'google-analytics'),
			'file_event' => __('File', 'google-analytics'),
			'audio_event' => __('Audio', 'google-analytics'),
			'video_event' => __('Video', 'google-analytics'),
			'signup_event' => __('Sign Up', 'google-analytics'),
			'custom_event' => __('Custom', 'google-analytics'),
			'click_event' => __('Click', 'google-analytics'),
			'download_event' => __('Download', 'google-analytics'),
			'submit_event' => __('Submit', 'google-analytics'),
			'success_event' => __('Success', 'google-analytics'),
			'l10n_print_after' => 'try{convertEntities(google_analyticsL10n);}catch(e){};'
		));
*/
	} # header_scripts()
	
	/**
	 * footer_scripts()
	 *
	 * @return void
	 **/

	function footer_scripts() {
		if ( $this->use_universal == false ) {
			$uacct = google_analytics::get_uacct();

			if ( !sem_google_analytics_debug && !$uacct )
				return;
		
echo <<<EOS

<script type="text/javascript">
try { var pageTracker = _gat._getTracker("$uacct");} catch(err) {}
</script>

EOS;
		}

		$ad_event = __('Ad Unit', 'google-analytics');
		$file_event = __('File', 'google-analytics');
		$audio_event = __('Audio', 'google-analytics');
		$video_event = __('Video', 'google-analytics');
		$signup_event = __('Sign Up', 'google-analytics');
		$custom_event = __('Custom', 'google-analytics');
		$click_event = __('Click', 'google-analytics');
		$download_event = __('Download', 'google-analytics');
		$submit_event = __('Submit', 'google-analytics');
		$success_event = __('Success', 'google-analytics');
		$l10n_print_after = 'try{convertEntities(google_analyticsL10n);}catch(e){};';

echo <<<EOS

<script type='text/javascript'>
/* <![CDATA[ */
var google_analyticsL10n = {
	"ad_event":"$ad_event",
	"file_event":"$file_event",
	"audio_event":"$audio_event",
	"video_event":"$video_event",
	"signup_event":"$signup_event",
	"custom_event":"$custom_event",
	"click_event":"$click_event",
	"download_event":"$download_event",
	"submit_event":"$submit_event",
	"success_event":"$success_event"
	};
$l10n_print_after;
/* ]]> */
</script>

EOS;

		do_action('google_analytics');
	} # footer_scripts()

	/**
	 * track_page()
	 *
	 * @return void
	 **/

	function track_page() {
		$uacct = google_analytics::get_uacct();


		if ( !$uacct ) {
			echo "\n" . '<!-- '
				. __('Configure Google Analytics under Settings / Google Analytics', 'google-analytics')
				. ' -->' . "\n";
			return;
		} elseif ( current_user_can('publish_posts') || current_user_can('publish_pages') ) {
			echo "\n" . '<!-- '
				. __('Google Analytics Notice: Authors, Editors and Admins are not tracked', 'google-analytics')
				. ' -->' . "\n";
			return;
		}

		if ( $this->use_universal ) {
			if ( is_404() ) {
				$tracker = 'ga("send", "pageview", "/404/?page=" + document.location.pathname + document.location.search + "&from=" + document.referrer);';
			} else {
				$tracker = "ga('send', 'pageview');";
			}
		}
		else {
			if ( is_404() ) {
				$tracker = '_gaq.push(["_trackPageview", "/404/?page=" + document.location.pathname + document.location.search + "&from=" + document.referrer]);';
			} else {
				$tracker = "_gaq.push(['_trackPageview']);";
			}
		}
		
		echo <<<EOS

<script type="text/javascript">
	$tracker
</script>

EOS;
	} # track_page()


    /**
     * track_media()
     *
     * @param $flashvars
     * @internal param $args
     * @return $args
     */

	function track_media($flashvars) {
		if ( !current_user_can('publish_posts') && !current_user_can('publish_pages') ) {
			$uacct = google_analytics::get_uacct();
			if ( $uacct ) {
				$flashvars['plugins'][] = 'gapro-1';
				$flashvars['gapro.accountid'] = $uacct;
			}
		}
		
		return $flashvars;
	} # track_media()
	
	
	/**
	 * get_uacct()
	 *
	 * @return array
	 **/

	function get_uacct() {
		$o = google_analytics::get_options();
        return $o['uacct'];

	} # get_uacct()
	
	
	/**
	 * get_options()
	 *
	 * @return array $options
	 **/

    static function get_options() {
		static $o;
		
		if ( !is_admin() && isset($o) )
			return $o;
		
		$o = get_option('google_analytics');

        if ( $o === false || !is_array($o) || !isset($o['subdomains']) || !isset($o['universal_analytics']) ) {
			$o = google_analytics::init_options();
		}
		
		return $o;
	} # get_options()
	
	
	/**
	 * init_options()
	 *
	 * @return array $options
	 **/

	static function init_options() {
        $defaults = array(
            'uacct' => 'Your Account ID',
            'subdomains' => false,
	        'universal_analytics' => true,
	        'displayTracking' => false,
      	);

        $o = get_option('google_analytics');
		
        if ( !$o ) {
    		$o  = $defaults;
        }
        elseif ( !is_array($o) ) {
            $uacct = $o;
            unset($o);
            $o['uacct'] = $uacct;
            $o['subdomains'] = false;
	        $o['universal_analytics'] = true;
	        $o['displayTracking'] = false;

            extract($o, EXTR_SKIP);
         	$o = compact(array_keys($defaults));
        }
		else
			$o = wp_parse_args($o, $defaults);

		update_option('google_analytics', $o);
		
		return $o;
	} # init_options()
	
	
	/**
	 * admin_menu()
	 *
	 * @return void
	 **/

	function admin_menu() {
		add_options_page(
			__('Google Analytics', 'google-analytics'),
			__('Google Analytics', 'google-analytics'),
			'manage_options',
			'google-analytics',
			array('google_analytics_admin', 'edit_options')
			);
	} # admin_menu()
} # google_analytics

$google_analytics = google_analytics::get_instance();