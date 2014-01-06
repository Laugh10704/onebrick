------------------------------------------------------------------------------
  User verification module Readme
  http://drupal.org/project/user_verify
  by David Herminghaus (doitDave) www.david-herminghaus.de
------------------------------------------------------------------------------

Contents:
=========
1. ABOUT
2. TECHNICAL
3. INTEGRATION/CUSTOMIZING
4. QUICK REFERENCE
5. KNOWN ISSUES

1. ABOUT
========

Drupal (at least: Drupal 6) makes it relatively easy for spammers to join your
site and annoy your serious users - as long as you do not set the registration
policy to "approve by admin" which either means much work for you or
frustrating waiting times for new users. This is because Drupal sends out
registration/verification mails directly after registration (with the same
server process, that is) which is mainly a consequence of the fact that not
every site owner may have custom cron jobs.

2. TECHNICAL
============

Instead of directly sending out a mail with an initial password it leaves
password choice to the user but generates an additional verification code.
This code may be sent out instantly as well or (recommended) with a
configurable delay. Also, the new user's status will be set to "inactive"
and remain in that state unless the user verifies properly.
Too many verification attempts (you may set a custom limit) will silently
and permanently lock out the applicant until an admin manually releases him.

3. INSTALLATION
==========================

* Make sure you have PHP 5 on your server.
* Unless already done, configure a standard Drupal cronjob. It should run
  at least once per 24 hours or, if you want delayed verification mails,
  often enough to deliver them within the appropriate time.
  (E.g. for a mail delay of 10 minutes, your cronjob should run every 10
  minutes.)
* Activate the module.

4. QUICK REFERENCE
==================

* Set up your extended verification parameters and individual verification
  mail template at admin/user/user_verify (note the available variables).
* Recommendedly alter your "Welcome, no approval required" mail template
  at admin/user/settings to inform your new users about their having to
  wait for the verification mail.

5. KNOWN ISSUES
===============

Mind the correlation between cron tasks and your individual delays:

* Always consider your configured delays and time frames a minimum value,
* always consider your cron intervals the maximum value.

E.g.: If you set a verification mail delay of 10 minutes but your cronjob is
set up to run every 15 minutes, the effective delay will be somewhere between
10 and 15 minutes.

Thus, if you want delivery and actions à la minute, your cronjob would have to
run every minute, which is not recommended unless you have your own dedicated
and, more important, very performant server.

