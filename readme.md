Kickstart
=========

A WordPress plugin to help agencies kickstart their open data efforts by allowing citizens to browse existing datasets and vote for suggested priorities.


What Problem This Solves
------------------------

The Open Data Policy requires agencies to engage with the public and see which datasets are more requested than others.  


How This Solves It
------------------
The 'kickstart' project takes the information in their data.json file and provides instant ability to enable this voting.


How it Works
------------

The plugin registers a custom post type "datasets" and two custom taxonomies, "agencies", and "statuses" to organize them. Visitors to the site will be presented with the opportunity to browse and search, upvote or downvote, and comment on datasets, both public and potential. 

Installation
------------

Install just like you would any other WordPress plugin, by placing in the `/wp-content/plugins/` directory and activating via the plugins menu

Suggested Usage
---------------

* Install on a standalone site or as a subsite of a WordPress multisite install
* Install and activate a third-party signon plugin (e.g., Google, facebook) to encourage voting and commenting
* Install plugins to encourage browsing such as the Faceted Search Widget, or Count Shourtcode
* Install Display Custom Fields to display the additional dataset information (such as agency and status)

