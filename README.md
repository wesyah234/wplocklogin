wplocklogin
===========

A simple script to lock down the Wordpress wp-login.php script, and a mechanism to temporarily unlock it when needed

INSTRUCTIONS:

Create a directory under your web root.  Name it something hard to guess, since anyone (or any script) that can guess this directory name can unlock your login page.

In this directory, git clone this repo.

This will create a wplocklogin directory.

Visit your website at: http://yourwebsite.com/yourcrypticdirname/wplocklogin

Follow the instructions on that page to either unlock your login page to login, unlock your login page to logout, or unlock your login page for the purpose of a Wordpress upgrade.  (since a wordpress upgrade will want access to your wp-login.php script and it must be available)
