# ImageAPI Optimize GD

This module moves GD compression/quality into an ImageAPI Optimize processor.
This allows for you to separate image actions like crop/scale/overlay from the
image optimization side.

It's encouraged to set your sitewide GD toolkit settings to 100% quality, and
allow ImageAPI Optimize pipelines to handle sitewide and per-image style quality
settings.

- For a full description of the module, visit the project page:
  [project page](https://www.drupal.org/project/imageapi_optimize_gd)

- To submit bug reports and feature suggestions, or to track changes:
  [issue queue](https://www.drupal.org/project/issues/imageapi_optimize_gd)


## Contents of this file

- Requirements
- Installation
- Configuration
- Maintainers


## Requirements

This module requires the following modules:

- [Image Optimize or ImageAPI Optimize](https://www.drupal.org/project/imageapi_optimize)


## Installation

Install as you would normally install a contributed Drupal module. Visit:
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

- Configure a new Image Optimize Pipeline

    - First, you will need to Configure a new Image Optimize Pipeline. To do
     this navigate to Configuration >> Media >> Image Optimize pipelines. Fill
     out Image optimize pipeline name. There is a dropdown list that reads
     Select a new processor, choose GD then click Add. On the next screen fill
     in the *Image quality* this represents a percentage. Make sure that the
     File Types has a type selected such JPEG. Click Add processor.

- Set a Sitewide default pipeline

    - You can set a Sitewide default pipeline that will load in automatically
      on all Image Styles using the Sitewide default pipeline. Navigate to
      Configuration >> Media >> Image Optimize pipelines. Select the preferred
      Pipeline. Click Save configuration.

- Set an individual Image style's Image Optimize Pipeline

    - Now when you travel to Configuration > Media > Image and edit your
     preferred Image style. At the bottom of the form you can select your
     preferred Image Optimize Pipeline or the Sitewide default pipeline.


## Maintainers

- Michael Lander - [michaellander](https://www.drupal.org/u/michaellander)

This project has been sponsored by:

- Elevated Third

Specializing in Drupal-powered digital experiences that get results. Visit
[Elevated Third](https://www.elevatedthird.com/).
