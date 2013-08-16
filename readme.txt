=== Google Analytics ===
Contributors: Denis-de-Bernardy, Mike_Koepke
Donate link: http://www.semiologic.com/partners/
Tags: semiologic, google, google analytics, analytics
Requires at least: 2.8
Tested up to: 3.6
Stable tag: trunk

Integrates Google Analytics on your site.


== Description ==

The Google Analytics (GA) plugin for WordPress lets you easily add Google's web tracking script to your site.

Google Analytics is a truly amazing service for marketers: it generates comprehensive site statistics for your site -- at no cost -- that are entirely geared towards website optimization. The only downside is that Google ends up knowing your stats. If you do not wish it to, look into the Hitslink plugin.

To make use of the plugin, browse Settings / Google Analytics and follow the instructions.

Note that site authors are not tracked so as to not pollute your stats.

This plugin contrasts with a number of similar WordPress plugins, in the amount of effort that was put into making it easy to use, and into integrating other plugins so you can make the best of your stats.

= AdSense integration =

AdSense integration is built-in. As soon as you tie your GA account to your AdSense account, GA will start tracking revenues and clicks, on a per keyword and on a per page basis.

To take full advantage of this, filter your GA reports using segments. Oftentimes, these will allow you to discover stunning -- not to mention unintuitive -- things about your site and its visitors.

= Ad Manager integration =

The Ad Manager plugin makes GA track any click on its ad widgets as GA events, provided the click doesn't occur in an iframe tag. (Their provider's AUP generally disallows to change their code.)

= Contact Form integration =

The Contact Form plugin makes GA track contact form usage as GA events.

= Mediacaster integration =

The Mediacaster plugin makes GA track file downloads and media player usage (play, stop, completed video or podcast, and so on) as GA events.

= Newsletter Manager integration =

The Newsletter Form plugin makes GA track subscription form usage as GA events. It will additionally segment users who subscribe to your mailing lists, allow you to track what they do once subscribed in a convenient report.

= 404 error and outbound link tracking =

Page not found errors on your site are tracked as /404/?page=...&from=...

Clicks on outbound links are tracked as /outbound/?to=...
as well as Outbound Links events

= Search tracking =

This is built into Google Analytics. Edit your site's profile in GA, and add the "s" parameter as a search query parameter.

= Domain-wide tracking =

If you wish to track your domain and all of its subdomains in a single report, you would check the 'Track Subdomains' setting for all domains.

To make the best of the resulting reports, customize the way your reports are displayed, in your main domain's profile, so as to be able to distinguish your various subdomains.

Suppose you have the following URLs that you want to track as a single entity:

To track the following 3 domains under example-petstore.com GA account

    www.example-petstore.com
    dogs.example-petstore.com
    cats.example-petstore.com

You would set your user ID for dogs.example-petstore.com and cats.example-petstore.com to the same Google user ID you used for www.example-petstore.com.

You then would check the 'Track Subdomains' setting in your Google Analytics Settings screen for all 3 of the domains.


= Custom event tracking =

The GA lastly allows to track custom-defined events, in case the need arises. Doing so is relatively easy, too: simply add a ga_event class to your <a>, <div> or <form> tag of interest.

= Help Me! =

The [Semiologic forum](http://forum.semiologic.com) is the best place to report issues.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Change Log ==

= 5.0 =

- Added option to turn on cross-domain tracking
- Outbound links are now tracked as Events as well as Pageviews
- File downloads, mailto: and tel: links now tracked as events
- Added Keyword Ranking tracking code per [A New Method to Track Keyword Ranking using Google Analytics](http://cutroni.com/blog/2013/01/14/a-new-method-to-track-keyword-ranking-using-google-analytics/)
- Internal links that start with http:// were incorrectly being tracked as outbound links
- Fixed conflict with wp-polls that caused the tracking code to be included twice
- Updated help links in the Settings screen to latest Google help information
- WP 3.6 compat
- PHP 5.4 compat

= 4.3 =

- WP 3.5 compat
- Changed sequence of subdomain tracking javascript code now that feature is in production

= 4.2 =

- Use the asynchronous tracker.

= 4.1.1 =

- WP 3.0.1 compat

= 4.1 =

- WP 3.0 compat

= 4.0.1 =

- Localization enhancements

= 4.0 =

- Complete rewrite
- Localization
- Event tracking
- Improved 404 and outbound link tracking
- Code enhancements and optimizations
