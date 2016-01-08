<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Controllers\EntityBrowserFormController.
 */

namespace Drupal\entity_browser\Controllers;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Controller\HtmlFormController;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Standalone entity browser page.
 */
class EntityBrowserFormController extends HtmlFormController implements ContainerInjectionInterface {

  /**
   * Current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The browser storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $browserStorage;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs Entity browser form controller.
   *
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param RouteMatchInterface $route_match
   *   Current route match service.
   * @param EntityManagerInterface $entity_manager
   *   Entity manager service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   */
  public function __construct(ControllerResolverInterface $controller_resolver, FormBuilderInterface $form_builder, ClassResolverInterface $class_resolver, RouteMatchInterface $route_match, EntityManagerInterface $entity_manager, Request $request) {
    parent::__construct($controller_resolver, $form_builder, $class_resolver);
    $this->currentRouteMatch = $route_match;
    $this->browserStorage = $entity_manager->getStorage('entity_browser');
    $this->request = $request;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('controller_resolver'),
      $container->get('form_builder'),
      $container->get('class_resolver'),
      $container->get('current_route_match'),
      $container->get('entity.manager'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormObject(RouteMatchInterface $route_match, $form_arg) {
    return $this->loadBrowser()->getFormObject();
  }

  /**
   * Standalone entity browser title callback.
   */
  public function title() {
    $browser = $this->loadBrowser();
    return Xss::filter($browser->label());
  }

  /**
   * Loads entity browser object for this page.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   Loads the entity browser object
   */
  protected function loadBrowser() {
    /** @var $route \Symfony\Component\Routing\Route */
    $route = $this->currentRouteMatch->getRouteObject();
    /** @var $browser \Drupal\entity_browser\EntityBrowserInterface */
    return $this->browserStorage->load($route->getDefault('entity_browser_id'));
  }

}
