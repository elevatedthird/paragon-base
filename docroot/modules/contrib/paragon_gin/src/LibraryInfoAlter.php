<?php

namespace Drupal\paragon_gin;

class LibraryInfoAlter {

  public function libraryInfoAlter(&$libraries, $extension) {
    if($extension == 'gin' && isset($libraries['tabs'])) {
      $libraries['tabs']['dependencies'][] = 'paragon_gin/tabs';
    }
    if($extension == 'claro') {
      $libraries['tabs'] = [
        'css' => [
          'component' => [
            'css/components/tabs.css' => [],
          ]
        ]
      ];
    }

    if($extension == 'paragon_gin') {
      $libraries['tabs']['dependencies'][] = 'claro/tabs';
    }
  }

}
