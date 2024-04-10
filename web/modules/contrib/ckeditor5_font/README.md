#CKEditor 5 - Font Plugin (Text Color, Background Color)
Integrates the Font plugin directly inside CKEditor 5 for Drupal 9 and 10.

Allows to control the text and background color directly inside the CKEditor 5 interface.
Allows to customize the color palette.

No external dependencies required, the plugin is integrated directly via DLL Builds.
Font Plugin version: v31.00
Only ``FontColor`` and ``FontBackgroundColor`` widgets are supported at this time.

More info: https://ckeditor.com/docs/ckeditor5/latest/features/font.html

``composer require "drupal/ckeditor5_font"``

###Installation
- Install and Enable CKEditor 5
- Create a rich text format:
``/admin/config/content/formats``
- Install this module
- Drag & drop the color and background widgets from to the active toolbar
- Optionally, specify your own color palette from the configuration panel

---
The project follow the trails of the old https://www.drupal.org/project/colorbutton project for CKEditor 4.
