<?php

namespace Drupal\field_tools\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\field\FieldStorageConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an overview of reference fields.
 */
class FieldReferencesList implements ContainerInjectionInterface, FormInterface {

  use StringTranslationTrait;
  use FieldListTrait;

  /**
   * Field types which are references.
   *
   * TODO: core should allow a way for field types to declare themselves as
   * such! There is an issue for this!
   */
  const REFERENCE_TYPES = [
    'image',
    'file',
    'entity_reference',
    'entity_reference_revisions',
    'dynamic_entity_reference',
  ];

  /**
   * Creates an FieldReferencesList object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(
    RequestStack $request_stack,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_bundle_info,
    EntityFieldManagerInterface $entity_field_manager,
    FormBuilderInterface $form_builder
  ) {
    $this->requestStack = $request_stack;
    $this->currentRoute = $route_match->getRouteName();
    $this->entityTypeManager = $entity_type_manager;
    $this->entityBundleInfo = $entity_bundle_info;
    $this->entityFieldManager = $entity_field_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('form_builder'),
    );
  }

  /**
   * Builds the page content.
   */
  function content() {
    // TODO: apparently this should be avoided.
    $query_params = \Drupal::request()->query->all();

    // Put the form container at the top; the form itself is built later so
    // data can be passed to it.
    $build['form_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Filters'),
      '#open' => TRUE,
    ];

    // Prepare the host entity bundle query parameter value.
    $host_entity_filter = [];
    $host_entity_bundle_filter = [];
    if (isset($query_params['host_entity_bundle'])) {
      foreach ($query_params['host_entity_bundle'] as $host_entity_bundle_query_value) {
        list($host_entity_bundle_query_value_entity_type_id, $host_entity_bundle_query_value_bundle_name) = explode(':', $host_entity_bundle_query_value, 2);

        if ($host_entity_bundle_query_value_bundle_name == ':all') {
          $host_entity_filter[$host_entity_bundle_query_value_entity_type_id] = TRUE;
        }
        else {
          // TODO: this doesn't work yet!
          $host_entity_bundle_filter[$host_entity_bundle_query_value_entity_type_id] = $host_entity_bundle_query_value_bundle_name;
        }
      }
    }

    $reference_storage_definitions = [];
    $referenced_types = [];
    /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->getGroup() != 'content') {
        continue;
      }

      $storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);

      // Filter to reference fields.
      $entity_reference_storage_definitions = array_filter($storage_definitions, function($storage_definition) {
        return in_array($storage_definition->getType(), static::REFERENCE_TYPES);
      });

      // Exclude references to the bundle entity.
      // TODO: consider adding filter in the form for this?
      if ($bundle_key = $entity_type->getKey('bundle')) {
        unset($entity_reference_storage_definitions[$bundle_key]);
      }

      // Keep a list of all referenced types to pass to the form.
      foreach ($entity_reference_storage_definitions as $storage_definition) {
        foreach (\Drupal::service('field_tools.references.info')->getReferencedTypes($storage_definition) as $referenced_type) {
          $referenced_types[$referenced_type] = TRUE;
        }
      }

      // Apply host entity query filter.
      // TODO: apply the bundle filter.
      if (isset($query_params['host_entity_bundle'])) {
        if (!isset($host_entity_filter[$entity_type_id]) && !isset($host_entity_bundle_filter[$entity_type_id])) {
          continue;
        }
      }

      // Apply referenced types query filter.
      if (isset($query_params['referenced_type'])) {
        $entity_reference_storage_definitions = array_filter($entity_reference_storage_definitions, function($storage_definition) use ($query_params) {
          return array_intersect(\Drupal::service('field_tools.references.info')->getReferencedTypes($storage_definition), $query_params['referenced_type']);
        });
      }

      // Prefix the field name with the entity type ID in the array of all
      // storage definitions, as field names are not unique across entity types.
      foreach ($entity_reference_storage_definitions as $field_name => $storage_definition) {
        $reference_storage_definitions[$entity_type_id . ':' . $field_name] = $storage_definition;
      }
    }

    // Apply a sort from the query parameter.
    if (isset($query_params['sort']) && in_array($query_params['sort'], ['type', 'field_name', 'entity_type', 'referenced_type'])) {
      switch ($query_params['sort']) {
        case 'type':
          uasort($reference_storage_definitions, function(FieldStorageDefinitionInterface $a, FieldStorageDefinitionInterface $b) {
            return strnatcmp($a->getType(), $b->getType());
          });
          break;

        case 'field_name':
          uasort($reference_storage_definitions, function(FieldStorageDefinitionInterface $a, FieldStorageDefinitionInterface $b) {
            return strnatcmp($a->getName(), $b->getName());
          });
          break;

        case 'entity_type':
          uasort($reference_storage_definitions, function(FieldStorageDefinitionInterface $a, FieldStorageDefinitionInterface $b) {
            return strnatcmp($a->getTargetEntityTypeId(), $b->getTargetEntityTypeId());
          });
          break;

        case 'referenced_type':
          uasort($reference_storage_definitions, function(FieldStorageDefinitionInterface $a, FieldStorageDefinitionInterface $b) {
            return strnatcmp($this->getReferencedType($a), $this->getReferencedType($b));
          });
          break;
      }
    }

    // Pass data to the form so it only shows options for the actual data,
    // e.g. only show options for the referenced types filter for types which
    // are referenced.
    $build['form_container']['form'] = $this->formBuilder->getForm($this, array_keys($referenced_types));

    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        Link::fromTextAndUrl(t('Field name'), $this->getSortQueryURL('field_name')),
        Link::fromTextAndUrl(t('Type'), $this->getSortQueryURL('type')),
        t('Cardinality'),
        Link::fromTextAndUrl(t('Entity type'), $this->getSortQueryURL('entity_type')),
        Link::fromTextAndUrl(t('Referenced type'), $this->getSortQueryURL('referenced_type')),
        t('Instances'),
        // t('Operations'),
      ],
    ];

    $rows = [];
    foreach ($reference_storage_definitions as $key => $storage_definition) {
      $row = $this->buildRow($storage_definition);
      $rows[$key] = $row;
    }
    $build['table'] += $rows;

    return $build;
  }

  /**
   * Returns a table row for a single field storage.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage.
   *
   * @return array
   *   The row render array.
   */
  protected function buildRow(FieldStorageDefinitionInterface $storage_definition): array {
    $entity_type_id = $storage_definition->getTargetEntityTypeId();
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    // $bundle_entity_type = $storage_definition->getBundleEntityType();

    $row = [];
    $row['name'] = [
      '#plain_text' => $storage_definition->getName(),
      //'#wrapper_attributes' => ['rowspan' => count($grouped_field_storage_configs)],
    ];
    $row['type'] = [
      '#type' => 'link',
      '#title' => $storage_definition->getType(),
      '#url' => Url::fromRoute('field_tools.reports.references', [], [
        // TODO: preserve existing query parameters!
        'query' => [
          'filter-type' => $storage_definition->getType(),
        ],
      ]),
    ];
    $cardinality = $storage_definition->getCardinality();
    if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $cardinality = $this->t('Unlimited');
    }
    $row['cardinality'] = [
      '#plain_text' => $cardinality,
    ];
    $row['entity_type'] = [
      '#plain_text' => $entity_type_id,
    ];

    $row['referenced_type'] = [
      '#type' => 'link',
      '#title' => $this->getReferencedType($storage_definition),
      '#url' => Url::fromRoute('field_tools.reports.references', [], [
        // TODO: preserve existing query parameters!
        'query' => [
          'filter-referenced-type' => $storage_definition->getType(),
        ],
      ]),

    ];

    // Get all the bundle instances, if not a base field.
    if ($storage_definition->isBaseField()) {
      $row['bundles'] = [
        '#plain_text' => $this->t("- base field - "),
      ];
    }
    elseif ($storage_definition instanceof FieldStorageConfigInterface) {
      $bundle_info = $this->entityBundleInfo->getBundleInfo($entity_type_id);
      $field_map = $this->entityFieldManager->getFieldMap();
      $bundle_entity_type = $entity_type->getBundleEntityType();

      $items = [];
      // The route for editing a field, provided by Field UI.
      $route_name = "entity.field_config.{$entity_type_id}_field_edit_form";

      foreach ($field_map[$entity_type_id][$storage_definition->getName()]['bundles'] as $bundle_name) {
        $route_parameters = [
          // TODO: dirty hack; get the name from the field config!
          'field_config' => implode('.', [$entity_type_id, $bundle_name, $storage_definition->getName()]),
        ];
        if (!empty($bundle_entity_type)) {
          $route_parameters[$bundle_entity_type] = $bundle_name;
        }
        $url = Url::fromRoute($route_name, $route_parameters);

         $items[$bundle_name] = Link::fromTextAndUrl($bundle_name, $url)->toString();
      }

      natcasesort($items);

      $row['bundles'] = [
        '#theme' => 'item_list',
        '#items' => $items,
      ];
    }
    // TODO: handle bundle field case.

    return $row;
  }

  /**
   * Builds operation links for a single storage definition.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage.
   *
   * @return array
   *   The build array.
   */
  protected function buildOperations(FieldStorageDefinitionInterface $storage_definition): array {
    $build = array(
      '#type' => 'operations',
      '#links' => $this->getOperations($entity),
    );

    return $build;
  }

  /**
   * Gets the referenced entity type for the field.
   *
   * TODO: improve this when
   * https://www.drupal.org/project/drupal/issues/3057545 is fixed.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage.
   *
   * @return string
   *   The referenced entity type ID.
   */
  protected function getReferencedType(FieldStorageDefinitionInterface $storage_definition): string {
    switch ($storage_definition->getType()) {
      case 'entity_reference':
      case 'entity_reference_revisions':
        return $storage_definition->getSettings()['target_type'];

      case 'image':
      case 'file':
        return 'file';

      // case 'dynamic_entity_reference':
      // TODO

      default:
        return 'unknown';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_tools_reference_list_filter';
  }

  /**
   * Form builder.
   *
   * @param array $referenced_types
   *   An array of the entity type IDs which are referenced by all the reference
   *   fields found (not just those currently filtered).
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $referenced_types = []) {
    $form['host_entity_bundle'] = $this->buildHostEntityBundleFilter();

    $entity_type_options = [];
    foreach ($referenced_types as $entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

      // Only look at content entities.
      if ($entity_type->getGroup() != 'content') {
        continue;
      }

      $entity_type_options[$entity_type_id] = $entity_type->getLabel();
    }

    $form['referenced_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Referenced types'),
      '#options' => $entity_type_options,
      '#default_value' => $this->requestStack->getCurrentRequest()->query->get('referenced_type') ?? [],
    ];

    // Preserve a current sort from the table headers in the filter submission.
    $form['sort'] = [
      '#type' => 'hidden',
      '#value' => $this->requestStack->getCurrentRequest()->query->get('sort') ?? '',
    ];

    $form['#method'] = 'get';

    $form['actions'] = $this->getFormActions();

    $form['#after_build'][] = [get_class($this), 'afterBuild'];

    $form['#attached']['library'][] = 'field_tools/filter_forms';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submit: 'GET' form.
  }

}
