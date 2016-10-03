<?php

namespace Drupal\Tests\entity_browser\FunctionalJavascript;

use Drupal\file\Entity\File;

/**
 * Entity Browser views widget tests.
 *
 * @group entity_browser
 * @see \Drupal\entity_browser\Plugin\EntityBrowser\Widget\View
 */
class EntityBrowserViewsWidgetTest extends EntityBrowserJavascriptTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'views',
    'entity_browser_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'access test_entity_browser_file entity browser pages',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests Entity Browser views widget.
   */
  public function testViewsWidget() {
    // Create a file so that our test View isn't empty.
    file_unmanaged_copy(\Drupal::root() . '/core/misc/druplicon.png', 'public://example.jpg');
    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $file->save();

    // Visit a test entity browser page that defaults to using a View widget.
    $this->drupalGet('/entity-browser/iframe/test_entity_browser_file');
    $field = 'entity_browser_select[file:' . $file->id() . ']';

    // Test exposed filters.
    $this->assertSession()->pageTextContains('example.jpg');
    $this->assertSession()->fieldExists($field);
    $this->getSession()->getPage()->fillField('filename', 'llama');
    $this->getSession()->getPage()->pressButton('Apply');
    $this->waitForAjaxToFinish();
    $this->assertSession()->fieldNotExists($field);
    $this->assertSession()->pageTextNotContains('example.jpg');
    $this->getSession()->getPage()->fillField('filename', 'example');
    $this->getSession()->getPage()->pressButton('Apply');
    $this->waitForAjaxToFinish();
    $this->assertSession()->pageTextContains('example.jpg');
    $this->assertSession()->fieldExists($field);

    // Test selection.
    $this->submitForm([
      $field => 1,
    ], t('Select entities'));
    $this->assertSession()->pageTextContains($file->getFilename());
  }

}
