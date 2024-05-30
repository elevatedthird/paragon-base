<?php

namespace Drupal\paragon_core;

class LocalTasksAlter {

  public function localTasksAlter(&$local_tasks) {
    foreach ($local_tasks as $id => $task) {
      if (isset($task['id'], $task['provider'])) {
        if ($task['id'] == 'layout_builder_ui' && $task['provider'] == 'layout_builder') {
          $local_tasks[$id]['title'] = t('Layout Builder');
        }
        if ($task['id'] == 'content_moderation.workflows' && $task['provider'] == 'content_moderation') {
          $local_tasks[$id]['title'] = t('Preview');
          $local_tasks[$id]['weight'] = 0;
        }
      }
    }
    if (isset($local_tasks['entity.node.version_history'])) {
      $local_tasks['entity.node.version_history']['title'] = t('Version History');
    }
    if (isset($local_tasks['entity.node.delete_form'])) {
      unset($local_tasks['entity.node.delete_form']);
    }
    if (isset($local_tasks['entity.node.edit_form'])) {
      $local_tasks['entity.node.edit_form']['weight'] = 2;
    }
  }

}
