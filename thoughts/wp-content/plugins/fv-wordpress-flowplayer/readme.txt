=== FV Wordpress Flowplayer ===
Contributors: FolioVision
Tags: video, flash, flowplayer
Requires at least: 2.0
Tested up to: 2.8.4
Stable tag: 0.9.15

Embed videos (FLV, H.264, and MP4) into posts or pages. Uses modified version of flowplayer (with removed FP logo and copyright notice).

== Description ==

FV Wordpress Flowplayer plugin is a free, easy-to-use, and complete solution for embedding FLV or MP4 videos into your posts or pages.

* Plugin is completely non-commercial. It contains modified opensource version of Flowplayer 3.1.3, with removed FP logo and copyright notice.
* Supported video formats are FLV, H.264, and MP4. Multiple videos can be displayed in ope post or page.
* Default options for all the embedded videos can be set in comprehensive administration menu.
* It is loosely based on Wordpress Flowplayer plugin. However, there are several improvements:

	1. Doesn't use jQuery, so there will be no future conflicts with other plugins.
	2. Usage is simpler and forgiving, making the plugin easier to use.
	3. It will never display any annoying flowplayer logos or copyrights over your videos.
	4. Allows user to display clickable splash screen at the beginning of video (which not only looks good, but improves the performance significantly).
	5. Allows user to display popup box after the video ends, with any HTML content (clickable links, images, styling, etc.)

== Installation ==

There aren't any special requirements for FV Wordpress Flowplayer to work, and you don't need to install any additional plugins.

   1. Download and unpack zip archive containing the plugin.
   2. Upload the fv-wordpress-flowplayer directory into wp-content/plugins/ directory of your wordpress installation.
   3. Make sure, that configuration file wpfp.conf is writable.
   4. Go into Wordpress plugins setup in Wordpress administration interface and activate FV Wordpress Flowplayer plugin.
   5. Optionally, if you want to embed videos denoted just by their filename, you can create the /videos/ directory located directly in the root of your domain and place your videos there. Otherwise, you would have to type in a complete URL of video files.

== Screenshots ==

1. Post containing modified flowplayer playing a video.
2. Adding three players with different arguments into a post.
3. Configuration menu for administrators.

== Changelog ==

= 0.9.15 =
* support for widget use and template use

= 0.9.14 =
* Added a possibility to forbid the popup boxes.
* Some output validation.
* Minor visual improvements.

= 0.9.13 =
* Added "Replay" and "Share" buttons to the popup box after video finishes.
* Some performance tweaks concerning popup box.

= 0.9.12 =
* First stable version ready to be published.
* Removed farbtastic colour picker using jQuery from settings menu. Substituted by jscolor.

== Configuration ==

Once the plugin is uploaded and activated, there will be a submenu of settings menu called FV Wordpress Flowplayer. In that submenu, you can modify following settings:

* AutoPlay - decides whether the video starts playing automatically, when the page/post is displayed.
* AutoBuffering - decides whether te video starts buffering automatically, when the page/post is displayed. If AutoPlay is set to true, you can ignore this setting.
* Popup Box - decides whether a popup box with "replay" and "share" buttons will be displayed when video ends.
* Colors of all the parts of flowplayer instances on page/post (controlbar, canvas, sliders, buttons, mouseover buttons, time and total time, progress and buffer sliders).

On the right side of this screen, you can see the current visual configuration of flowplayer. If you click Apply Changes button, this player's looks refreshes.

