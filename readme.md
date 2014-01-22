Sociate
=======

Simple social sharing and analytics for WordPress.

For Developers
==============

How social data is stored
-------------------------

Currently, data is stored as post metadata in individual fields, all prefixed with 'sociate'. For example,
the social count for Twitter would be stored in a post metadata field `sociate-twitter`, and the trending score would be stored in a field
`sociate-trending`. Count data is stored in this way to allow for easy ordering and loop creation via `WP_Query()`;



For Users
=========

