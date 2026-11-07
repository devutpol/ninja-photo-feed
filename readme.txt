=== Ninja Photo Feed ===
Contributors: devutpol
Tags: instagram, instagram feed, instagram gallery, instagram widget, photo feed
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show your Instagram photos and videos on your WordPress site in a clean, responsive feed, set up from your dashboard.

== Description ==

Ninja Photo Feed displays the posts from your Instagram account on your WordPress site. Connect your account, pick a template, adjust the layout in a live preview, and place the feed anywhere with a shortcode or the Elementor widget.

= Features =

* **Connect in a few clicks** – sign in with Instagram, or connect through Facebook if your Instagram account is linked to a Facebook Page. You don't need to create your own Meta developer app.
* **Live editor** – every change is previewed before you save.
* **Two templates** in a responsive grid, with separate column counts for desktop, tablet and mobile, and adjustable gaps.
* **Choose what to show** – up to 10 posts per feed, newest or oldest first, and all posts, photos only or videos only.
* **Profile header** – show your profile picture, username, name and bio, in a classic or minimal layout.
* **Captions and clicks** – show or hide captions; when a post is clicked, do nothing, play the video in place, or open the post on Instagram.
* **Fast pages** – feeds are cached in your database, and you choose how long before new posts are fetched.
* **Optimize Images** – store resized copies of feed images on your own server, so pages load faster and visitors' browsers don't load images from Instagram.
* **Place feeds anywhere** – with the `[ninja_photo_feed]` shortcode or the Elementor widget.

Ninja Photo Feed is not affiliated with, endorsed or sponsored by Instagram or Meta Platforms, Inc.

== Installation ==

1. In your WordPress admin, go to **Plugins → Add New**, search for "Ninja Photo Feed", then install and activate it.
2. Open **Ninja Photo Feed** in the admin menu and connect your Instagram account.
3. Create a feed, choose a template, and adjust it in the editor.
4. Copy the feed's shortcode, for example `[ninja_photo_feed id="123" platform="instagram"]`, into any page or post. You can also use the Ninja Photo Feed widget in Elementor.

== Frequently Asked Questions ==

= Which Instagram accounts can I connect? =

Instagram **Business** or **Creator** (professional) accounts. Instagram no longer lets apps read personal accounts: Meta retired the Instagram Basic Display API on December 4, 2024. Switching a personal account to a Creator account is free and takes a minute in the Instagram app.

= Why does connecting go through wpsocialninja.com? =

Instagram only gives access tokens to a registered Meta app. Our connect service runs that app for you, so you don't have to create one. It passes the login result back to your site. Your posts are then fetched straight from Instagram by your own site. See "External services" below for exactly what is sent.

= My new posts don't appear yet. =

Feeds are cached so your pages stay fast. New posts show up when the cache expires, which you can change under **Settings → Global Feeds Settings**. You can also clear the cache there to fetch new posts right away.

= Can I show more than one feed? =

Yes. Create as many feeds as you like, each with its own shortcode and settings.

= What happens to my data if I delete the plugin? =

Your feeds and settings are kept, so they come back if you install the plugin again.

= How can I stop visitors' browsers from contacting Instagram? =

Turn on **Optimize Images** under **Settings → Global Feeds Settings**. Feed images are then stored on your own server and served from there.

== External services ==

This plugin connects to the following third-party services. Nothing is sent until an administrator connects an Instagram account.

= WP Social Ninja connect service =

Used to connect your Instagram account securely without you creating your own Meta developer app.

* When: only when an administrator clicks "Connect" (your browser is sent to connect.wpsocialninja.com). Access tokens are renewed by your site directly with Instagram, not through this service.
* What is sent when connecting: your site's admin URL (to bring you back), one-time login codes, and the admin's email address and display name.
* The email address is stored only if the admin ticks "Receive Get started emails" on the connect page. They can edit the address or untick the option there.
* What is not stored: access tokens (they are passed back to your site encrypted) and IP addresses.
* [Terms](https://wpsocialninja.com/terms-conditions/), [Privacy Policy](https://wpsocialninja.com/privacy-policy/)

= Instagram / Meta Graph API =

Used to fetch your Instagram posts and profile information, and to renew access tokens.

* When: after an account is connected, whenever a feed's cache expires, during scheduled token checks, and when an embedded post is looked up.
* What is sent: the connected account's access token and user ID, and the list of fields requested.
* Provided by Meta Platforms, Inc.: [Meta Platform Terms](https://developers.facebook.com/terms/), [Instagram Terms of Use](https://help.instagram.com/581066165581870), [Privacy Policy](https://www.facebook.com/privacy/policy/)

= Instagram image servers (Meta CDN) =

Feed images are served from Instagram's servers. Unless Optimize Images is turned on (images are then stored on your own server), visitors' browsers load each image directly from Meta, which receives the visitor's IP address and browser information.

* Provided by Meta Platforms, Inc.: [Privacy Policy](https://www.facebook.com/privacy/policy/)

== Source Code ==

The admin app and front-end scripts in `assets/` are compiled. Their uncompiled source (Vue, JavaScript and SCSS) and the build configuration are public at [github.com/devutpol/ninja-photo-feed](https://github.com/devutpol/ninja-photo-feed).

To build them, run `npm install`, then `npm run build`. The compiled files are written to `assets/`.

== Changelog ==

= 1.0.0 =
* Initial release.
