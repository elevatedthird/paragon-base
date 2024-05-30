<?php

namespace Drupal\Tests\list_predefined_options\Kernel;

use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the basic operation of list options plugins.
 *
 * @group list_predefined_options
 */
class ListOptionsTest extends KernelTestBase {

  use RequestTrait;
  use UserCreationTrait;

  /**
   * The modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'entity_test',
    'field',
    'field_ui',
    'options',
    'list_predefined_options',
    'list_predefined_options_test',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->entityDisplayRepository = $this->container->get('entity_display.repository');

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
  }

  /**
   * Tests the basic operation of list options plugins.
   */
  public function testFieldListOptions() {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = $this->entityTypeManager->getStorage('field_storage_config')->create([
      'field_name' => 'test_options_list',
      'entity_type' => 'entity_test',
      'type' => 'list_integer',
    ]);
    $field_storage->save();
    $field = $this->entityTypeManager->getStorage('field_config')->create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ]);
    $field->save();

    $user = $this->createUser(['administer entity_test content']);
    $this->setCurrentUser($user);

    $request = Request::create('entity_test/structure/entity_test/fields/entity_test.entity_test.test_options_list/storage');
    $response = $this->doRequest($request);
    $this->assertEquals(200, $response->getStatusCode());

    // The field storage config form has an element for selecting the list
    // options plugin.
    $select_element = $this->xpath('//*[@id="edit-list-predefined-options-plugin-id"]');
    $this->assertNotFalse($select_element);
    $select_element = reset($select_element);

    $option_elements = $this->getAllOptions($select_element);
    $this->assertNotEmpty($option_elements);
    $options = [];
    foreach ($option_elements as $element) {
      $options[(string) $element['value'][0]] = (string) $element;
    }

    // The form element shows the 'test_dozen' plugin.
    $this->assertArrayHasKey('test_dozen', $options);
    $this->assertContains('Test Dozen', $options);

    $this->doFormSubmit(
      'entity_test/structure/entity_test/fields/entity_test.entity_test.test_options_list/storage',
      [
        'list_predefined_options_plugin_id' => 'test_dozen',
      ]
    );

    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = $this->reloadEntity($field_storage);
    $settings = $field_storage->getSettings();
    $this->assertEmpty($settings['allowed_values']);
    $this->assertEquals('list_predefined_options_allowed_values', $settings['allowed_values_function']);

    $this->assertEquals('test_dozen', $field_storage->getThirdPartySetting('list_predefined_options', 'plugin_id'));
    $this->assertEquals('workaround-3016895', $field_storage->getThirdPartySetting('list_predefined_options_test', 'list_predefined_options_dummy'));

    // Configure the field to show on the default display.
    $display = $this->entityDisplayRepository->getFormDisplay('entity_test', 'entity_test', 'default');
    $display->setComponent('test_options_list')
      ->save();

    // Test that the options from the plugin are shown in the entity form.
    $request = Request::create('entity_test/add');
    $response = $this->doRequest($request);
    $this->assertEquals(200, $response->getStatusCode());

    $select_element = $this->xpath('//*[@id="edit-test-options-list"]');
    $this->assertNotFalse($select_element);
    $select_element = reset($select_element);

    $option_elements = $this->getAllOptions($select_element);
    $this->assertNotEmpty($option_elements);
    $options = [];
    foreach ($option_elements as $element) {
      $options[(string) $element['value'][0]] = (string) $element;
    }
    foreach (range(1, 12) as $value) {
      $this->assertArrayHasKey($value, $options);
    }

    // Change the field storage back to a custom values list, and check that
    // all of our settings are removed from the field storage config.
    $this->doFormSubmit(
      'entity_test/structure/entity_test/fields/entity_test.entity_test.test_options_list/storage',
      [
        'list_predefined_options_plugin_id' => '',
      ]
    );

    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = $this->reloadEntity($field_storage);
    $settings = $field_storage->getSettings();
    $this->assertEmpty($settings['allowed_values']);
    $this->assertEquals('', $settings['allowed_values_function']);

    $this->assertEmpty($field_storage->getThirdPartySettings('list_predefined_options'));
    $this->assertEmpty($field_storage->getThirdPartySettings('list_predefined_options_test'));
  }

  protected function reloadEntity(EntityInterface $entity) {
    $controller = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
    $controller->resetCache([$entity->id()]);
    return $controller->load($entity->id());
  }

}
