=== Neoforum ===
Contributors: saeros1984
Tags: forum, forums, forum plugin, WordPress forum plugin, community, discussion
Requires at least: 5.1
Tested up to: 5.0
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Neoforum is full-fledged forum engine for Wordpress, including all standard forum functionality.

= Currently available features =

* Full-fledged forum engine, can be used for communities of any size.
* Super responsive forum on all kind of devices.
* Built-in forum/topic subscription. 
* Drag and Drop forum management system.
* Topic and Post front-end moderation.
* Four moderators ranks with different rights.
* Can be enabled:
 * Premoderation of new topics.
 * Marking topics as solved.
 * Switching on/off links in forum posts.
 * User files attachment.
 * Guests can be allowed to reply in topics.
* Forum topics and posts Read / Unread logging.
* WordPress .MO/.PO translation files.

== Installation ==

1. Upload the entire `neoforum` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress. All needed SQL tables and 

options will be installed.
3. Go to 'Neoforum' options page (on the left panel), configure forums as you want and enjoy.

== Frequently Asked Questions ==

= How to change forum page? =

Use option "Forum page URL" on general Neoforum options page.

= How to change forum theme? =

There is only one theme available for now. But you can make your own, and enable it in "Forum 

theme" option on general Neoforum options page.

= Are guests allowed to reply? =

By deafault - no, but it can be changed on general Neoforum options page.

= Can users insert links in their replies? =

By default - no, but this can be allowed on general Neoforum options page. When allowed, all 

links will be marked with rel="nofollow".

= A there files attachment? =

Yes. On general Neoforum options page you can disable this, or contol max file size for user 

attachments and max folder size for each user.

= How can I manage forums? =

On "Forums" section in Neoforum admin panel. You can create new forums and sections using 

buttons on the top of the page. When cheated, you can edit titles and descriptions, add 

moderators, change forums order or make subforums by dragging and drop them.

= What means that buttons on the forums blocks? =

Lock will forbid any new posts or edits in the forum (and all subforums of it). Only 

administratos or moderators will be able to post.
Eye option make forum (and all subforums) restricted. It will be hidden from all, expect 

administators, moderators, and users added to list "Users who can read restricted forum".
Trash can will remove entire forum (but not subforums)

= What about other menu pages? =

In "Trash" menu you can search and manage posts and topics deleted from the forum by 

moderators.You can restore them or delete forever.
In "Users" menu you can assign or remove admonistrators or supermoderators, ban or unban users.
Reports" menu displays reports sent by users. Each moderator will only see reports from forums 

he currently moderating.

= What user ranks can be assigned? =

Administrator - all administrators of the Wordpress site automatically have administrator 

status on the forum. Administrator can be also assigned in "Users" menu by other administrator. 

It won't give him admin rights on the site, though. Administrator can ban and unban users, 

assign administrators and moderators, edit users profiles, edit, move, remove posts and topics 

on forum. However, managing Neoforum options still require administrator rights on the site.
Supermoderators can manage posts and topics on all forums, can ban users.
Moderators can be assigned in "Forums" menu, they can manage forums they allowed.


== Screenshots ==

1. Forum list | Screenshot #1
2. Drag and Drop forum management system. | Screenshot #2
3. Topics in forum | Screenshot #3
4. Opened topic | Screenshot #4


== Changelog ==

Released!