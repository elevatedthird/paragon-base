<?php

/**
 * @file
 * Hooks provided by the list_predefined_options module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Perform alterations on List Options definitions.
 *
 * @param array &$info
 *   Array of information on List Options plugins.
 */
function hook_list_options_info_alter(array &$info) {
  // Change the class of the 'foo' plugin.
  $info['foo']['class'] = SomeOtherClass::class;
}

/**
 * @} End of "addtogroup hooks".
 */
