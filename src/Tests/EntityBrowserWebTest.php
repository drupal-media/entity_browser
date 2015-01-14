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
use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity_browser forms
 *
 * @group entity_browser
 */
class EntityBrowserWebTest extends WebTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'user', 'entity_browser', 'entity_browser_test', 'entity_browser_example');
  
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
  
  protected function testDropdownChange() {
//     $this->installConfig(array('entity_browser', 'entity_browser_test', 'entity_browser_example'));
//     $this->installConfig(array('entity_browser'));
//     $this->installConfig(array('entity_browser_test'));
//     $this->installConfig(array('entity_browser_example'));
    
    $this->drupalGet('node/add/entity_browser_example');
    $this->assertText('Select Entities');
    
    $this->clickLink('Select Entities');
    
    $this->assertText('Test entity browser for files');
  }
  
}