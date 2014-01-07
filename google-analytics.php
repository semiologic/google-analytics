<?php
/*
Plugin Name: Google Analytics
Plugin URI: http://www.semiologic.com/software/google-analytics/
Description: Adds <a href="http://analytics.google.com">Google analytics</a> to your blog, with various advanced tracking features enabled.
Version: 5.1
Author: Denis de Bernardy & Mike Koepke
Author URI: http://www.getsemiologic.com
Text Domain: google-analytics
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.
**/


load_plugin_textdomain('google-analytics', false, dirname(plugin_basename(__FILE__)) . '/lang');

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

    /**
     * google_analytics()
     */
	public function __construct() {
        if ( !is_admin() ) {
        	add_action('wp_enqueue_scripts', array($this, 'header_scripts'));
        	add_action('wp_footer', array($this, 'footer_scripts'), 20);
        	add_action('wp_footer', array($this, 'track_page'), 1000); // after script manager
        	add_action('mediacaster_audio', array($this, 'track_media'));
        	add_action('mediacaster_video', array($this, 'track_media'));
        } else {
        	add_action('admin_menu', array($this, 'admin_menu'));
        }
    } # google_analytics()

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
		if ( $useSubdomains )
			$ga_domain = "\n_gaq.push(['_setDomainName', '" . $domain . "']);";
		
		echo <<<EOS
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

		if ( !sem_google_analytics_debug && ( !$uacct || current_user_can('publish_posts') || current_user_can('publish_pages') ) )
			return;
		
		$folder = plugin_dir_url(__FILE__);
		wp_enqueue_script('google_analytics', $folder . 'js/scripts.js', array('jquery'), '20140107', true);
		
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
	} # header_scripts()
	
	/**
	 * footer_scripts()
	 *
	 * @return void
	 **/

	function footer_scripts() {
		$uacct = google_analytics::get_uacct();
		
		if ( !sem_google_analytics_debug && !$uacct )
			return;
		
echo <<<EOS

<script type="text/javascript">
try { var pageTracker = _gat._getTracker("$uacct");} catch(err) {}
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
		
		if ( is_404() ) {
			$tracker = '_gaq.push(["_trackPageview", "/404/?page=" + document.location.pathname + document.location.search + "&from=" + document.referrer]);';
		} else {
			$tracker = "_gaq.push(['_trackPageview']);";
		}
		
		echo <<<EOS

<script type="text/javascript">
try { $tracker } catch(err) {}
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

        if ( $o === false || !is_array($o) || !isset($o['subdomains'])) {
			$o = google_analytics::init_options();
		}
		
		return $o;
	} # get_options()
	
	
	/**
	 * init_options()
	 *
	 * @return array $options
	 **/

	function init_options() {
        $defaults = array(
            'uacct' => 'Your Account ID',
            'subdomains' => false,
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

            extract($o, EXTR_SKIP);
         	$o = compact(array_keys($defaults));
        }

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


function google_analytics_admin() {
	include_once dirname(__FILE__) . '/google-analytics-admin.php';
}

add_action('load-settings_page_google-analytics', 'google_analytics_admin');

$google_analytics = new google_analytics();
