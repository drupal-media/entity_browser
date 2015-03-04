<?php

/**
 * Contains \Drupal\entity_browser_entity_form\Plugin\EntityBrowser\Widget\EntityForm.
 */

namespace Drupal\entity_browser_entity_form\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\inline_entity_form\InlineEntityFormPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "entity_form",
 *   label = @Translation("Entity form"),
 *   description = @Translation("Provides entity form widget.")
 * )
 */
class EntityForm extends WidgetBase {

  /**
   * Inline entity form plugin manager.
   *
   * @var \Drupal\inline_entity_form\InlineEntityFormPluginManager
   */
  protected $inlineFormManager;

  /**
   * Constructs widget plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\inline_entity_form\InlineEntityFormPluginManager
   *   Inline entity form plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, InlineEntityFormPluginManager $inline_form_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_manager);
    $this->inlineFormManager = $inline_form_manager;
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
      $container->get('plugin.manager.inline_entity_form')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    /** @var \Drupal\inline_entity_form\InlineEntityFormControllerInterface $inline_form_controller */
    $inline_form_controller = $this->inlineFormManager->createInstance('entity:node', []);

    $form = [];
    $form = $inline_form_controller->entityForm($form, $form_state);


    /** @var \Drupal\Core\Entity\EntityForm $controller */
    /*$controller = $this->entityManager->getFormObject('node', 'default');

    $entity = $this->entityManager->getStorage('node')->create([
      'type' => 'article'
    ]);

    $controller->setEntity($entity);

    $form = [];
    $child_form_state = new FormState();
    $child_form_state->set('form_display', $this->entityManager->getStorage('entity_form_display')->load('node.article.default'));
    $form = $controller->buildForm($form, $child_form_state);

    //unset($form['#parents']);
    //unset($form['#process']);
    $form['#process'] = [
      $form['#process'][1],
    ];*/

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {

  }

}
