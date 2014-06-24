jQuery(document).ready(function() {
	jQuery('div.ga_event, form.ga_event, form.signup_event, div.signup_event, form.form_event, div.form_event, div.ad_event, a.ga_event, a.download_event').each(function() {
		var t = jQuery(this);
		var category = t.children('input.event_category:first').val();
		var action = t.children('input.event_action:first').val();
		var label = t.children('input.event_label:first').val();
		
		if ( !category && t.is('.signup_event') )
			category = google_analyticsL10n.signup_event;
		
		if ( t.is('div.form_event, div.signup_event') ) {
			t = t.find('form:first');
			if ( !t.size() )
				return;
		}
		
		if ( !action ) {
			if ( t.hasClass('ad_event') ) {
				if ( !category )
					category = google_analyticsL10n.ad_event;
				action = google_analyticsL10n.click_event;
			} else if ( t.hasClass('download_event') ) {
				if ( !category )
					category = google_analyticsL10n.file_event;
				action = google_analyticsL10n.download_event;
				if ( !label )
					label = t.text();
			} else if ( t.is('form') ) {
				if ( !category )
					category = google_analyticsL10n.form_event;
				action = google_analyticsL10n.submit_event;
			} else {
				action = google_analyticsL10n.click_event;
			}
		}
		
		if ( !category )
			category = google_analyticsL10n.custom_event;
		
		if ( !label ) {
			if ( t.is('a') && t.attr('href') )
				label = t.attr('href');
			else if ( t.is('form') && t.attr('action') )
				label = t.attr('action');
			else if ( t.is('iframe') && t.attr('src') )
				label = t.attr('src');
			else if ( t.attr('id') )
				label = t.attr('id');
		}
		
		if ( t.is('form') ) {
			t.submit(function(e) {
				if ( e.isDefaultPrevented() )
					return false;
				
				if ( !label ) {
					try {
						ga('send', 'event', category, action);
					} catch ( err ) {}
				} else {
					var count = jQuery(this).attr('ga_count');
					count = count ? parseInt(count) + 1 : 1;
					jQuery(this).attr('ga_count', count);
					try {
						ga('send', 'event', category, action, label, count);
					} catch ( err ) {}
				}
				
				return true;
			});
		} else {
			t.click(function(e) {
				if ( !label ) {
					try {
						ga('send', 'event', category, action);
					} catch ( err ) {}
				} else {
					var count = jQuery(this).data('ga_count');
					count = count ? parseInt(count) + 1 : 1;
					jQuery(this).data('ga_count', count);
					try {
						ga('send', 'event', category, action, label, count);
					} catch ( err ) {}
				}
			});
		}
	});

    var baseHref = '';
    if (jQuery('base').attr('href') != undefined)
        baseHref = jQuery('base').attr('href');

    jQuery('a').click(function(e) {
   		var t = jQuery(this);
        if (!(!t.hasClass('ga_event') && !t.hasClass('download_event'))) {
        } else {
            var filetypes = /\.(zip|exe|dmg|pdf|doc.*|xls.*|ppt.*|mpp|mp3|mp4|m4a|rtf|txt|rar|tar|gzip|gz|wma|mov|avi|wmv|flv|swf|wav)/i;
            var href = (typeof(t.attr('href')) != 'undefined' ) ? t.attr('href') : "";
            var track = true;
            var isExternal = function(url) {
                return !(location.href.replace("http://", "").replace("https://", "").split("/")[0] === url.replace("http://", "").replace("https://", "").split("/")[0]);
            };
            var ev = {};
            ev.value = 0;
            ev.non_i = false;
            if (href.match(/^mailto:/i)) {
                ev.category = "Email";
                ev.action = "click";
                ev.label = href.replace(/^mailto:/i, '');
                ev.loc = href;
            }
            else if (filetypes.test(href)) {
                var extension = (/[.]/.exec(href)) ? /[^.]+$/.exec(href) : undefined;
                ev.category = "Download";
                ev.action = "click-" + extension[0];
                ev.label = href.replace(/ /g, "-");
                ev.loc = baseHref + href;
            }
            else if (href.match(/^https?:/i) && isExternal(href) && !href.match(/window.google_analytics_regexp/)) {
                ev.category = "Outgoing Link";
                ev.action = "click";
                ev.label = href.replace(/^https?:\/\//i, '');
                ev.non_i = true;
                ev.loc = href;
                try {
                    ga('send', 'pageview', '/outbound/?to=' + href);
                } catch (err) {
                }
            }
            else if (href.match(/^tel:/i)) {
                ev.category = "Telephone";
                ev.action = "click";
                ev.label = href.replace(/^tel:/i, '');
                ev.loc = href;
            }
            else
                track = false;

            if (track) {
                try {
                    ga('send', 'event', ev.category, ev.action, ev.label, ev.value, ev.non_i);
                    /*                    if ( t.attr('target') == undefined || t.attr('target').toLowerCase() != '_blank') {
                     setTimeout(function() { location.href = ev.loc; }, 400);
                     return false;
                     }
                     */
                } catch (err) {
                }
            }

        }
   	});


    //  This is Rank Tracker code.  It will track the organic page rank of keywords.
    // A New Method to Track Keyword Ranking using Google Analytics
    // http://cutroni.com/blog/2013/01/14/a-new-method-to-track-keyword-ranking-using-google-analytics/

    if (document.referrer.match(/google\./gi) && document.referrer.match(/cd/gi)) {
      var myString = document.referrer;
      var r        = myString.match(/cd=(.*?)&/);
      var rank     = parseInt(r[1]);
      var kw       = myString.match(/q=(.*?)&/);

      if (kw[1].length > 0) {
        var keyWord  = decodeURI(kw[1]);
      } else {
        keyWord = "(not provided)";
      }

      var p        = document.location.pathname;
	  ga('send', 'event', 'RankTracker', keyWord, p, rank, true);
    }

});
