=== Schedule Unpublish ===
Contributors:      revnoah
Donate link:       https://donate.noahjstewart.com
Tags:              post
Requires at least: 4.7
Tested up to: 7.1
Stable tag: 1.0.1
Requires PHP:      7.0
License:           GPL-3.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

A plugin to schedule an unpublish date and time field when editing posts. A scheduled task checks every fifteen minutes and moves up to 50 due posts to Pending per run.

== Description ==

A plugin to schedule an unpublish date and time field when editing posts. A scheduled task checks every fifteen minutes and moves up to 50 due posts to Pending per run.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Tools->Unpublish Queue screen to view posts in the queue to be unpublished.

== Frequently Asked Questions ==

= Does this plugin also schedule publishing a post? =

No, that functionality is already built into WordPress. You can do this by scheduling the post date in the future.

== Screenshots ==

1. The Schedule Unpublish field is added as a meta box on the edit post screen.
2. The post Unpublish Queue can be viewed under the Tools menu.

== Changelog ==

= 1.0.1 =
* Check for due posts every fifteen minutes. Each run moves up to 50 due posts to Pending, oldest first.
* Index scheduled posts for the worker and Unpublish Queue. Existing schedules are indexed in batches on upgrade.
* Keep posts published until their scheduled unpublish time in the site's WordPress timezone. Future posts remain visible in the Unpublish Queue. Skip missing or invalid dates.
* Verify the submitted nonce and post-edit permissions before saving a schedule. Skip autosaves and revisions.
* Updated the Tested up to declaration for WordPress 7.1.

= 0.1.0 =
* Release

Initial release.

== Upgrade Notice ==

= 0.1.0 =
* Release

Initial release.
