<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Tests\EntityBrowserTest.
 */

namespace Drupal\entity_browser\Tests;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\entity_browser\EntityBrowserDisplayInterface;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_browser\EntityBrowserSelectionDisplayInterface;
use Drupal\entity_browser\EntityBrowserWidgetInterface;
use Drupal\entity_browser\EntityBrowserWidgetSelectorInterface;
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
  public static $modules = array('entity_browser');

  /**
   * The entity browser storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface.
   */
  protected $controller;

  protected function setUp() {
    parent::setUp();

    $this->controller = $this->container->get('entity.manager')->getStorage('entity_browser');
    $this->widget_uuid = $this->container->get('uuid')->generate();
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
      'widget_selector' => 'tabs',
      'widget_selector_configuration' => array(),
      'widgets' => array(
        $this->widget_uuid => array(
          'id' => 'view',
          'label' => 'View widget',
          'uuid' => $this->widget_uuid,
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
      'langcode' => $this->container->get('language_manager')->getDefaultLanguage()->id,
      'status' => TRUE,
      'dependencies' => array(),
      'name' => 'test_browser',
      'label' => 'Testing entity browser instance',
      'display' => 'standalone',
      'display_configuration' => array(),
      'selection_display' => 'no_display',
      'selection_display_configuration' => array(),
      'widget_selector' => 'tabs',
      'widget_selector_configuration' => array(),
      'widgets' => array(
        $this->widget_uuid => array(
          'id' => 'view',
          'label' => 'View widget',
          'uuid' => $this->widget_uuid,
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
    $entity = $this->controller->load('test_browser');

    $this->assertTrue($entity instanceof EntityBrowserInterface, 'The loaded entity is an entity browser.');

    // Verify several properties of the entity browser.
    $this->assertEqual($entity->label(), 'Testing entity browser instance');
    $this->assertTrue($entity->uuid());
    $plugin = $entity->getDisplay();
    $this->assertTrue($plugin instanceof EntityBrowserDisplayInterface, 'Testing display plugin.');
    $this->assertEqual($plugin->getPluginId(), 'standalone');
    $plugin = $entity->getSelectionDisplay();
    $this->assertTrue($plugin instanceof EntityBrowserSelectionDisplayInterface, 'Testing selection display plugin.');
    $this->assertEqual($plugin->getPluginId(), 'no_display');
    $plugin = $entity->getWidgetSelector();
    $this->assertTrue($plugin instanceof EntityBrowserWidgetSelectorInterface, 'Testing widget selector plugin.');
    $this->assertEqual($plugin->getPluginId(), 'tabs');
    $plugin = $entity->getWidget($this->widget_uuid);
    $this->assertTrue($plugin instanceof EntityBrowserWidgetInterface, 'Testing widget plugin.');
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

}
