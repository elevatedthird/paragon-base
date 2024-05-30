<?php

namespace Drupal\field_tools\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Fhaculty\Graph\Graph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Outputs a graph of entity reference fields.
 */
class FieldGraph implements ContainerInjectionInterface, FormInterface {

  use StringTranslationTrait;
  use FieldListTrait;

  /**
   * Creates a FieldGraph object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(
    RequestStack $request_stack,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_bundle_info,
    FormBuilderInterface $form_builder
    ) {
    $this->requestStack = $request_stack;
    $this->currentRoute = $route_match->getRouteName();
    $this->entityTypeManager = $entity_type_manager;
    $this->entityBundleInfo = $entity_bundle_info;
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
      $container->get('form_builder'),
    );
  }

  /**
   * Builds the page content.
   */
  function content() {
    $build = [];

    $build['form_container']['form'] = $this->formBuilder->getForm($this);

    if (!\Drupal::service('module_handler')->moduleExists('mermaid')) {
      $build['message'] = [
        '#markup' => $this->t('This feature requires Graph API and Mermaid modules.'),
      ];
      return $build;
    }

    $graph_nodes = [];
    $graph_nodes_ids_by_entity_bundle = [];
    $index = 0;

    $reference_types_filter = $this->requestStack->getCurrentRequest()->query->get('reference_types');
    $reference_field_definitions = \Drupal::service('field_tools.references.info')->getReferenceFields(
      !empty($reference_types_filter['files']),
      !empty($reference_types_filter['owners']),
      !empty($reference_types_filter['config'])
    );

    $reference_targets = [];

    foreach ($reference_field_definitions as $field_definition) {
      $field_referenced_bundles =  \Drupal::service('field_tools.references.info')->getReferencedBundles($field_definition);

      foreach ($field_referenced_bundles as $target) {
        $reference_targets[$target] = TRUE;
      }
    }

    $reference_targets = array_keys($reference_targets);

    // Create the list of graph nodes.
    $graph_nodes = [];
    $graph = new Graph();
    foreach ($reference_field_definitions as $key => $field_definition) {
      $source_vertex_name = str_replace(':', '-', substr($key, 0, strrpos($key, ':')));

      if (!$graph->hasVertex($source_vertex_name)) {
        $graph_nodes[$source_vertex_name] = $graph->createVertex($source_vertex_name);
      }

      $field_referenced_bundles = \Drupal::service('field_tools.references.info')->getReferencedBundles($field_definition);
      foreach ($field_referenced_bundles as $target_vertex_name) {
        $target_vertex_name = str_replace(':', '-', $target_vertex_name);

        if (!$graph->hasVertex($target_vertex_name)) {
          $graph_nodes[$target_vertex_name] = $graph->createVertex($target_vertex_name);
        }
        $edge = $graph_nodes[$source_vertex_name]
          ->createEdgeTo($graph_nodes[$target_vertex_name]);

        $edge->setAttribute('title', $field_definition->getLabel());

        if ($field_definition->isRequired()) {
          $edge->setAttribute('second-required', TRUE);
        }
        if ($field_definition->getFieldStorageDefinition()->getCardinality() != 1) {
          $edge->setAttribute('second-multiple', TRUE);
        }
      }
    }

    $build['graph'] = [
      '#type' => 'graph',
      '#graph' => $graph,
      '#format' => 'mermaid_er',
      '#width' => '800',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_tools_reference_graph_filter';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['reference_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t("Reference types"),
      '#options' => [
        'owners' => $this->t("Include owner fields"),
        'files' => $this->t("Include file references"),
        'config' => $this->t("Include references to config entity types"),
      ],
      '#default_value' => $this->requestStack->getCurrentRequest()->query->get('reference_types') ?? [],
    ];

    $form['#method'] = 'get';

    $form['actions'] = $this->getFormActions();

    $form['#after_build'][] = [get_class($this), 'afterBuild'];

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
