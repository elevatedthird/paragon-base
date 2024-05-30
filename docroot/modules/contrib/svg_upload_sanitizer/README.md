CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Similar module
 * Maintainers

INTRODUCTION
------------

The SVG Upload Sanitizer module provides a simple way to sanitize
uploaded svg.

Every uploaded svg is automatically sanitize.

To sanitize SVG this module rest upon the
[darylldoyle/svg-sanitizer]((#darylldoyle/svg-sanitizer))
package.

REQUIREMENTS
------------

The module requires the following package:

 * SVG Sanitizer: https://github.com/darylldoyle/svg-sanitizer

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See
   https://www.drupal.org/docs/8/extending-drupal-8/installing-modules
   for further information.

CONFIGURATION
-------------

This module has no opinion regarding the configuration of the
[darylldoyle/svg-sanitizer](#darylldoyle/svg-sanitizer)
package.

The default behavior of 
[darylldoyle/svg-sanitizer](#darylldoyle/svg-sanitizer)
is used.

With the current implementation it should already be possible to configure
[darylldoyle/svg-sanitizer](#darylldoyle/svg-sanitizer)
just by decorating the service.
For instance to remove references to remote files you just have to call the
method provided by
[darylldoyle/svg-sanitizer](#darylldoyle/svg-sanitizer) like:

```yaml
# mymodule.services.yml

services:
  mymodule.sanitizer.svg:
    decorates: svg_upload_sanitizer.sanitizer.svg
    class: enshrined\svgSanitize\Sanitizer
    calls:
      - [removeRemoteReferences, [TRUE]]
```

SIMILAR MODULE
--------------

 * SVG Sanitizer: https://www.drupal.org/project/svg_sanitizer
   It sanitizes the file using a field formatter.

MAINTAINERS
-----------

Current maintainers:
 * Benjamin Rambaud (beram) - https://drupal.org/user/3508624

_Links:_

* [darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer) <a name="darylldoyle/svg-sanitizer"/>
