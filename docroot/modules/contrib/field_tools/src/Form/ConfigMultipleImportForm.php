<?php

namespace Drupal\field_tools\Form;

use Drupal\Core\Config\ConfigImporterException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\field_tools\FieldCloner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\StorageComparer;

/**
 * Provides a form to import multiple fields to base field code.
 *
 * This allow fields initially created in the admin UI to be converted to base
 * fields.
 *
 * @todo Use ConfirmFormBase.
 */
class ConfigMultipleImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_tools_config_multiple_import_form';
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import'] = [
      '#title' => $this->t('Paste your configuration here. WARNING: This currently only works with config exported from a bundle field tools config export.'),
      '#type' => 'textarea',
      '#rows' => 24,
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $import = $form_state->getValue('import');

    // Split up into different YAMLs.
    $data = explode("\n", $import);
    $import_items = [];
    $current = [];
    // ARGH this is really ugly! Refactor!
    foreach ($data as $line) {
      // Start a fresh YAML and take the config ID.
      if (str_starts_with($line, '_config_id')) {
        if ($current) {
          $import_items[] = $current;
        }

        $current = [];
        $current['config_id'] = trim(explode(':', $line)[1]); // !! todo
        continue;
      }

      $current['yaml'][] = $line;
    }
    // Add the last one.
    $import_items[] = $current;

    $config_importer_factory = \Drupal::service('field_tools.config_importer_factory');
    $this->configStorage = \Drupal::service('config.storage');
    $source_storage = new StorageReplaceDataWrapper($this->configStorage);

    $imported = [];
    foreach ($import_items as $import_item) {
      $config_item_data = Yaml::decode(implode("\n", $import_item['yaml']));
      // @todo Handle errors.

      $config_name = $import_item['config_id'];

      $source_storage->replaceData($config_name, $config_item_data);
    }

    $storage_comparer = new StorageComparer($source_storage, $this->configStorage);
    $storage_comparer->createChangelist();
    $config_importer = $config_importer_factory->createConfigImporter($storage_comparer);

    try {
      $config_importer->validate();
    }
    catch (ConfigImporterException $config_exception) {
      \Drupal::service('messenger')->addError($this->t("Configuration was not imported. See reasons below."));
      foreach ($config_importer->getErrors() as $error) {
        \Drupal::service('messenger')->addError($error);
      }

      return;
    }

    $config_importer->import();

    \Drupal::service('messenger')->addMessage($this->t("Config was imported."));
    // TODO: restore detailed message!
    // if ($imported) {
    //   \Drupal::service('messenger')->addMessage($this->t("Imported entities: @list.", [
    //     '@list' => implode(', ', $imported),
    //   ]));
    // }
    // else {
    //   \Drupal::service('messenger')->addMessage($this->t("Nothing was imported."));
    // }
  }

}
