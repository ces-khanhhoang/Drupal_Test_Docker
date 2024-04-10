# Webform Spam Words

  It is a simple module for spam keywords validation on Webforms.
  Administrators can provide the ability to add spam keywords,
  Error message, Webform field name.

# Installation

  1. Place the module folder in your modules folder
  2. Make sure you have the webform module enabled
  3. Activate the module via admin/modules

# Usage Steps

  1. Enable the "Webform Spam Words" module
  2. Go to configuration: /admin/config/webform/webform-spam-words
     and add the keywords, error message and field name.
  3. Go to the respective webform and add the handlers.
     For example: /admin/structure/webform/manage/contact/handlers
  4. Click on "Add handler" button to add "Webform Spam Words"
     for the specific webforms.

# Configuration page:
  url: /admin/config/webform/webform-spam-words

  Once finished clear your drupal caches.

# Author

- ThirstySix  (https://www.drupal.org/user/824886)
