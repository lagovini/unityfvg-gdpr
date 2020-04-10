CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------
This module provides permissions for managing block content and block content
types.

The following permissions are added:
- Administer block content types.
  This enables granting a user permission to manage block content types.

- View restricted block content.
  This enables granting a user permission to view restricted block content that
  the user cannot manage on the "Custom block library" page.

- Permissions for each block content type.
  This enabled granting a user permission to manage block content for a given
  type in the following ways:
  - Create new block content.
  - Delete any block content.
  - Edit any block content.

REQUIREMENTS
------------

No requirements for this module.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

After enabling this module, users without the specific permissions will not be
able to manage block content or block content types.

Assign the permissions to each role you wish.

MAINTAINERS
-----------

Current maintainers:
 * Joshua Roberson - https://www.drupal.org/u/joshuaroberson
