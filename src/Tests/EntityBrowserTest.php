<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Tests\EntityBrowserTest.
 */

namespace Drupal\entity_browser\Tests;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Form\FormState;
use Drupal\entity_browser\DisplayInterface;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_browser\WidgetInterface;
use Drupal\entity_browser\WidgetSelectorInterface;
use Drupal\entity_browser\SelectionDisplayInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the entity_browser config entity
 *
 * @group entity_browser
 */
class EntityBrowserTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'user', 'entity_browser', 'entity_browser_test');

  /**
   * The entity browser storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface.
   */
  protected $controller;

  /**
   * Pre-generated UUID.
   *
   * @var string
   */
  protected $widgetUUID;

  /**
   * Route provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  protected function setUp() {
    parent::setUp();

    $this->controller = $this->container->get('entity.manager')->getStorage('entity_browser');
    $this->widgetUUID = $this->container->get('uuid')->generate();
    $this->routeProvider = $this->container->get('router.route_provider');
  }

  /**
   * Tests CRUD operations.
   */
  public function testEntityBrowserCRUD() {
    $this->assertTrue($this->controller instanceof ConfigEntityStorage, 'The entity_browser storage is loaded.');

    // Run each test method in the same installation.
    $this->createTests();
    $this->loadTests();
    $this->deleteTests();
  }

  /**
   * Tests the creation of entity_browser.
   */
  protected function createTests() {
    $plugin = array(
      'name' => 'test_browser',
      'label' => 'Testing entity browser instance',
      'display' => 'standalone',
      'display_configuration' => array(),
      'selection_display' => 'no_display',
      'selection_display_configuration' => array(),
      'widget_selector' => 'single',
      'widget_selector_configuration' => array(),
      'widgets' => array(
        $this->widgetUUID => array(
          'id' => 'view',
          'label' => 'View widget',
          'uuid' => $this->widgetUUID,
          'settings' => array(),
        ),
      ),
    );

    foreach (array('display' => 'getDisplay', 'selection_display' => 'getSelectionDisplay', 'widget_selector' => 'getWidgetSelector') as $plugin_type => $function_name) {
      $current_plugin = $plugin;
      unset($current_plugin[$plugin_type]);

      // Attempt to create an entity_browser without required plugin.
      try {
        $entity = $this->controller->create($current_plugin);
        $entity->{$function_name}();
        $this->fail('An entity browser without required ' . $plugin_type . ' created with no exception thrown.');
      }
      catch (PluginException $e) {
        $this->assertEqual('The "" plugin does not exist.', $e->getMessage(), 'An exception was thrown when an entity_browser was created without a ' . $plugin_type . ' plugin.');
      }
    }

    // Try to create an entity browser w/o the ID.
    $current_plugin = $plugin;
    unset($current_plugin['name']);
    try {
      $entity = $this->controller->create($current_plugin);
      $entity->save();
      $this->fail('An entity browser without required name created with no exception thrown.');
    }
    catch (EntityMalformedException $e) {
      $this->assertEqual('The entity does not have an ID.', $e->getMessage(), 'An exception was thrown when an entity_browser was created without a name.');
    }

    // Create an entity_browser with required values.
    $entity = $this->controller->create($plugin);
    $entity->save();

    $this->assertTrue($entity instanceof EntityBrowserInterface, 'The newly created entity is an Entity browser.');

    // Verify all of the properties.
    $actual_properties = $this->container->get('config.factory')->get('entity_browser.browser.test_browser')->get();

    $this->assertTrue(!empty($actual_properties['uuid']), 'The entity browser UUID is set.');
    unset($actual_properties['uuid']);

    // Ensure that default values are filled in.
    $expected_properties = array(
      'langcode' => $this->container->get('language_manager')->getDefaultLanguage()->getId(),
      'status' => TRUE,
      'dependencies' => array(),
      'name' => 'test_browser',
      'label' => 'Testing entity browser instance',
      'display' => 'standalone',
      'display_configuration' => array(),
      'selection_display' => 'no_display',
      'selection_display_configuration' => array(),
      'widget_selector' => 'single',
      'widget_selector_configuration' => array(),
      'widgets' => array(
        $this->widgetUUID => array(
          'id' => 'view',
          'label' => 'View widget',
          'uuid' => $this->widgetUUID,
          'settings' => array(),
        ),
      ),
    );

    $this->assertIdentical($actual_properties, $expected_properties, 'Actual config properties are structured as expected.');
  }

  /**
   * Tests the loading of entity browser.
   */
  protected function loadTests() {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity */
    $entity = $this->controller->load('test_browser');

    $this->assertTrue($entity instanceof EntityBrowserInterface, 'The loaded entity is an entity browser.');

    // Verify several properties of the entity browser.
    $this->assertEqual($entity->label(), 'Testing entity browser instance');
    $this->assertTrue($entity->uuid());
    $plugin = $entity->getDisplay();
    $this->assertTrue($plugin instanceof DisplayInterface, 'Testing display plugin.');
    $this->assertEqual($plugin->getPluginId(), 'standalone');
    $plugin = $entity->getSelectionDisplay();
    $this->assertTrue($plugin instanceof SelectionDisplayInterface, 'Testing selection display plugin.');
    $this->assertEqual($plugin->getPluginId(), 'no_display');
    $plugin = $entity->getWidgetSelector();
    $this->assertTrue($plugin instanceof WidgetSelectorInterface, 'Testing widget selector plugin.');
    $this->assertEqual($plugin->getPluginId(), 'single');
    $plugin = $entity->getWidget($this->widgetUUID);
    $this->assertTrue($plugin instanceof WidgetInterface, 'Testing widget plugin.');
    $this->assertEqual($plugin->getPluginId(), 'view');
  }

  /**
   * Tests the deleting of entity browser.
   */
  protected function deleteTests() {
    $entity = $this->controller->load('test_browser');

    // Ensure that the storage isn't currently empty.
    $config_storage = $this->container->get('config.storage');
    $config = $config_storage->listAll('entity_browser.browser.');
    $this->assertFalse(empty($config), 'There are entity browsers in config storage.');

    // Delete the entity browser.
    $entity->delete();

    // Ensure that the storage is now empty.
    $config = $config_storage->listAll('entity_browser.browser.');
    $this->assertTrue(empty($config), 'There are no entity browsers in config storage.');
  }

  /**
   * Tests dynamic routes.
   */
  protected function testDynamicRoutes() {
    $this->installConfig(array('entity_browser_test'));
    $this->installSchema('system', 'router');
    $this->container->get('router.builder')->rebuild();

    /** @var $entity \Drupal\entity_browser\EntityBrowserInterface */
    $entity = $this->controller->load('test');
    $route = $entity->route();

    $this->assertEqual($route->getPath(), '/entity-browser/test', 'Dynamic path matches.');
    $this->assertEqual($route->getDefault('entity_browser_id'), $entity->id(), 'Entity browser ID matches.');
    $this->assertEqual($route->getDefault('_content'), 'Drupal\entity_browser\Controllers\StandalonePage::page', 'Controller matches.');
    $this->assertEqual($route->getDefault('_title_callback'), 'Drupal\entity_browser\Controllers\StandalonePage::title', 'Title callback matches.');
    $this->assertEqual($route->getRequirement('_permission'), 'access ' . String::checkPlain($entity->id()) . ' entity browser pages', 'Permission matches.');

    try {
      $registered_route = $this->routeProvider->getRouteByName('entity_browser.' . $entity->id());
    }
    catch (\Exception $e) {
      $this->assert('fail', t('Expected route not found: @message', array('@message' => $e->getMessage())));
      return;
    }

    $this->assertEqual($registered_route->getPath(), '/entity-browser/test', 'Dynamic path matches.');
    $this->assertEqual($registered_route->getDefault('entity_browser_id'), $entity->id(), 'Entity browser ID matches.');
    $this->assertEqual($registered_route->getDefault('_content'), 'Drupal\entity_browser\Controllers\StandalonePage::page', 'Controller matches.');
    $this->assertEqual($registered_route->getDefault('_title_callback'), 'Drupal\entity_browser\Controllers\StandalonePage::title', 'Title callback matches.');
    $this->assertEqual($registered_route->getRequirement('_permission'), 'access ' . String::checkPlain($entity->id()) . ' entity browser pages', 'Permission matches.');
  }

  /**
   * Tests dynamically generated permissions.
   */
  protected function testDynamicPermissions() {
    $this->installConfig(array('entity_browser_test'));
    $permissions = $this->container->get('user.permissions')->getPermissions();

    /** @var $entity \Drupal\entity_browser\EntityBrowserInterface */
    $entity = $this->controller->load('test');

    $expected_permission_name = 'access ' . String::checkPlain($entity->id()) . ' entity browser pages';
    $expected_permission = array(
      'title' => $this->container->get('string_translation')->translate('Access @name pages', array('@name' => $entity->label())),
      'description' => $this->container->get('string_translation')->translate('Access pages that %browser uses to operate.', array('%browser' => $entity->label())),
      'provider' => 'entity_browser',
    );

    $this->assertIdentical($permissions[$expected_permission_name], $expected_permission, 'Dynamically generated permission found.');
  }

  /**
   * Test single widget selector.
   */
  protected function testSingleWidgetSelector() {
    $this->installConfig(array('entity_browser_test'));

    /** @var $entity \Drupal\entity_browser\EntityBrowserInterface */
    $entity = $this->controller->load('test');

    $widget = $entity->getWidgetSelector()->getCurrentWidget($entity->getWidgets());
    $this->assertEqual($widget->label(), 'View widget nr. 1', 'First widget is active.');

    // Change weight and expect second widget to become first.
    $widget->setWeight(3);
    $new_widget = $entity->getWidgetSelector()->getCurrentWidget($entity->getWidgets());
    $this->assertEqual($new_widget->label(), 'View widget nr. 2', 'Second widget is active after changing weights.');
  }

/**
   * Test drop_down widget selector.
   */
  protected function testDropDownWidgetSelector() {
    $this->installConfig(array('entity_browser_test'));

    /** @var $entity \Drupal\entity_browser\EntityBrowserInterface */
    $entity = $this->controller->load('test_dropdown');

    $widget = $entity->getWidgetSelector()->getCurrentWidget($entity->getWidgets());
    $this->assertEqual($widget->label(), 'Upload', 'First widget is active.');

    // Change weight and expect second widget to become first.
    $widget->setWeight(3);
    $new_widget = $entity->getWidgetSelector()->getCurrentWidget($entity->getWidgets());
    $this->assertEqual($new_widget->label(), 'View widget nr. 2', 'Second widget is active after changing weights.');
  }

  /**
   * Test selected event dispatch.
   */
  protected function testSelectedEvent() {
    $this->installConfig(array('entity_browser_test'));

    /** @var $entity \Drupal\entity_browser\EntityBrowserInterface */
    $entity = $this->controller->load('dummy_widget');
    $entity->getWidgets()->current()->entity = $entity;

    $form_state = new FormState();
    $form = [];
    $form = $entity->buildForm($form, new $form_state);
    $entity->submitForm($form, $form_state);

    // Event should be dispatched from widget and added to list of selected entities.
    $selected_entities = $entity->getSelectedEntities();
    $this->assertEqual($selected_entities, [$entity], 'Expected selected entities detected.');
  }
}
