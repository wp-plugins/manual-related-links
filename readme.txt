=== Manual Related Links ===
Contributors: aaroncampbell
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal%40xavisys%2ecom&item_name=Manual%20Related%20Links&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: related,links,posts
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.0.0

A related links plugin that allows you to manually specify links, even if they're on another site. Requires PHP5.

== Description ==

This plugin allows you to manually enter links that are related to a post.  They
can be on any site and you can enter just the URL or an entire link (specifying
the title attribute, onclicks, etc).  There are helper functions to display them
in your theme or you can simply tell the plugin to automatically add them to
posts or even just single posts.  The list is output as an unordered list (ul)
with an optional title, and everything has CSS classes to make styling easy.
Requires PHP5.

== Installation ==

1. Verify that you have PHP5, which is required for this plugin.
1. Upload the whole `manual-related-links` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Manually integrate into your theme or go to the plugin settings page and set it to automatically add them to posts

== Frequently Asked Questions ==

= Why aren't the related posts showing? =

You need to manually integrate into your theme using the helper functions (wp_get_related_links and wp_related_links) or go to the plugin settings page and set it to automatically add them to posts

= Can I craft my own HTML for the links? =

Yes, you can enter just the URL or an entire link (specifying the title attribute, onclicks, etc).
