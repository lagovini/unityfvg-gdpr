CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Recommended Modules
* Installation
* Configuration
* Troubleshooting
* Maintainers


INTRODUCTION
------------

The Block Content Permissions module adds permissions for administering
"block content types" and "block content". The "Administer blocks" permission
normally manages these entities on the "Custom block library" pages:
"Block types" and "Blocks".

* For additional information on the module visit:
  https://www.drupal.org/project/block_content_permissions

* To submit bug reports, suggest features, or track changes visit:
  https://www.drupal.org/project/issues/block_content_permissions


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

* Block Region Permissions:
  Adds permissions for administering the "Block layout" pages.
  https://www.drupal.org/project/block_region_permissions


INSTALLATION
------------

Install this module as you would normally install a contributed Drupal module.
Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the user permissions in `Administration » People » Permissions`:

  - Block - Administer blocks

    (Required) Allows management of blocks. **Warning:** This permission grants
    access to block pages not managed by this module. Use the recommended
    modules to restrict the rest. Requirements for this permission have been
    removed for most pages, so it is not required for some use cases. It is
    still required for navigational purposes and the "Blocks" views page.

  - Block Content Permissions - [*type*]: Create new block content

    (Optional) Create block content for a specific type. View on "Blocks" page.

  - Block Content Permissions - [*type*]: Delete any block content

    (Optional) Delete block content for a specific type. View on "Blocks" page.

  - Block Content Permissions - [*type*]: Edit any block content

    (Optional) Edit block content for a specific type. View on "Blocks" page.

  - Block Content Permissions - Administer block content types

    (Optional) Give to **trusted roles only**. Allows management of all block
    content types. The "Field UI" permissions fully manage the displays, fields,
    and form displays.

  - Block Content Permissions - View restricted block content

    (Optional) Allows viewing and searching of block content for all types.
    Disabling this permission restricts the types to ones the user can manage.
    This permission is only used on the "Blocks" views page and will not affect
    the "Create", "Edit" and "Delete" restrictions. The views page requires the
    "Administer blocks" permission.

  - Field UI - Custom block: Administer display

    (Optional) Give to **trusted roles only**. Allows management of displays for
    all block content types.

  - Field UI - Custom block: Administer fields

    (Optional) Give to **trusted roles only**. Allows management of fields for
    all block content types.

  - Field UI - Custom block: Administer form display

    (Optional) Give to **trusted roles only**. Allows management of form display
    for all block content types.

  - System - Use the administration pages and help

    (Optional) Allows use of admin pages during navigation.

  - System - View the administration theme

    (Optional) Allows use of administrative theme for aesthetics.

  - Toolbar - Use the toolbar

    (Optional) Allows use of toolbar during navigation.


TROUBLESHOOTING
---------------

List of pages that should deny access depending on permissions.

**"Block types" pages ("Administer block content types" permission):**
* **List:**
  - **Path:** /admin/structure/block/block-content/types
  - **Route:** entity.block_content_type.collection
* **Add:**
  - **Path:** /admin/structure/block/block-content/types/add
  - **Route:** block_content.type_add
* **Edit:**
  - **Path:** /admin/structure/block/block-content/manage/{block_content_type}
  - **Route:** entity.block_content_type.edit_form
* **Delete:**
  - **Path:** /admin/structure/block/block-content/manage/{block_content_type}/delete
  - **Route:** entity.block_content_type.delete_form

**"Blocks" pages ("Create", "Delete", "Edit" and "View" permissions):**
* **List:**
  - **Path:** /admin/structure/block/block-content
  - **Route:** entity.block_content.collection
  - **Route:** view.block_content.page_1
* **Add:**
  - **Path:** /block/add
  - **Route:** block_content.add_page
* **Add type:**
  - **Path:** /block/add/{block_content_type}
  - **Route:** block_content.add_form
* **Edit:**
  - **Path:** /block/{block_content}
  - **Route:** entity.block_content.canonical
  - **Route:** entity.block_content.edit_form
* **Delete:**
  - **Path:** /block/{block_content}/delete
  - **Route:** entity.block_content.delete_form


MAINTAINERS
-----------

Current maintainers:
* Joshua Roberson - https://www.drupal.org/u/joshuaroberson
