<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\View.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\entity_browser\WidgetBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "view",
 *   label = @Translation("View"),
 *   description = @Translation("Uses a view to provide entity listing in a browser's widget.")
 * )
 */
class View extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view' => NULL,
      'view_display' => NULL,
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Constructs a new View object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
   public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, AccountInterface $current_user) {
     parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_manager);
     $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    $form = [];
    // TODO - do we need better error handling for view and view_display (in case
    // either of those is nonexistent or display not of correct type)?
    /** @var \Drupal\views\ViewExecutable $view */

    $form['#attached']['library'] = ['entity_browser/view'];

    $view = $this->entityManager
      ->getStorage('view')
      ->load($this->configuration['view'])
      ->getExecutable();

    // Add exposed filter values, if present
    foreach ($form_state->getUserInput() as $name => $value) {
      if (strpos($name, 'entity_browser_exposed_') === 0) {
        $name = str_replace('entity_browser_exposed_', '', $name);
        $view->exposed_data[$name] = $value;
      }
    }

    if (!empty($this->configuration['arguments'])) {
      if (!empty($aditional_widget_parameters['path_parts'])) {
        $arguments = [];
        // Map configuration arguments with original path parts.
        foreach ($this->configuration['arguments'] as $argument) {
          $arguments[] = isset($aditional_widget_parameters['path_parts'][$argument]) ? $aditional_widget_parameters['path_parts'][$argument] : '';
        }
        $view->setArguments(array_values($arguments));
      }
    }

    $form['view'] = $view->executeDisplay($this->configuration['view_display']);

    $ids = [];
    foreach ($view->result as $row_id => $row_result) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $row_result->_entity;
      $ids[$row_id] = [
        'id' => $entity->id(),
        'type' => $entity->getEntityTypeId(),
      ];
    }
    $form_state->set('view_widget_rows', $ids);

    if (empty($view->field['entity_browser_select'])) {
      $url = Url::fromRoute('entity.view.edit_form',['view'=>$this->configuration['view']])->toString();
      if ($this->currentUser->hasPermission('administer views')) {
        return [
          '#markup' => t('Entity browser select form field not found on a view. <a href=":link">Go fix it</a>!', [':link' => $url]),
        ];
      }
      else {
        return [
          '#markup' => t('Entity browser select form field not found on a view. Go fix it!'),
        ];
      }
    }

    // When rebuilding makes no sense to keep checkboxes that were previously
    // selected.
    if (!empty($form['view']['entity_browser_select']) && $form_state->isRebuilding()) {
      foreach (Element::children($form['view']['entity_browser_select']) as $child) {
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\entity_browser\Plugin\EntityBrowser\Widget\View', 'processCheckbox'];
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\Core\Render\Element\Checkbox', 'processAjaxForm'];
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\Core\Render\Element\Checkbox', 'processGroup'];
      }
    }

    $form['view']['exposed_widgets']['filter'] = [
      'submit' => [
        '#type' => 'button',
        '#value' => t('Filter'),
        '#name' => 'filter',
      ],
    ];

    // Add exposed widgets from the view, if present.
    if (!empty($form['view']['view']['#view']->exposed_widgets)) {
      $form['view']['exposed_widgets'] += $form['view']['view']['#view']->exposed_widgets;
      $form['view']['exposed_widgets']['#weight'] = -1;
      unset($form['view']['view']['#view']->exposed_widgets);

      // Add exposed filter default values from the form state
      foreach ($form_state->getUserInput() as $name => $value) {
        if (strpos($name, 'entity_browser_exposed_') === 0) {
          $form['view']['exposed_widgets'][$name]['#value'] = $value;
        }
      }
    }
    // Hide the filter button from view.
    else {
      // We are using this for pagers too so let's keep it in form.
      $form['view']['exposed_widgets']['filter']['submit']['#attributes']['class'][] = 'visually-hidden';
    }

    $form['view']['view'] = [
      '#markup' => \Drupal::service('renderer')->render($form['view']['view']),
    ];

    return $form;
  }

  /**
   * Sets the #checked property when rebuilding form.
   *
   * Every time when we rebuild we want all checkboxes to be unchecked.
   *
   * @see \Drupal\Core\Render\Element\Checkbox::processCheckbox()
   */
  public static function processCheckbox(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#checked'] = FALSE;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $selected_rows = array_keys(array_filter($form_state->getValue('entity_browser_select')));
    $entities = [];
    $ids = $form_state->get('view_widget_rows');
    foreach ($selected_rows as $row) {
      $entities[] = $this->entityManager->getStorage($ids[$row]['type'])->load($ids[$row]['id']);
    }

    $this->selectEntities($entities, $form_state);
  }

}
