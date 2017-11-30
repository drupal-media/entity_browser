<?php

namespace Drupal\Tests\entity_browser\FunctionalJavascript;

/**
 * Tests the entity_browser.
 *
 * @group entity_browser
 */
class EntityBrowserTest extends EntityBrowserJavascriptTestBase {

  /**
   * Tests single widget selector.
   */
  public function testSingleWidgetSelector() {

    // Sets the single widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file');

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'single', 'Widget selector is set to single.');

    // Create a file.
    $image = $this->createFile('llama');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');

    // Switch back to the main page.
    $this->getSession()->switchToIFrame();
    $this->waitForAjaxToFinish();
    // Test the Edit functionality.
    $this->assertSession()->pageTextContains('llama.jpg');
    $this->assertSession()->buttonExists('Edit');
    // @TODO Test the edit button.
    // Test the Delete functionality.
    $this->assertSession()->buttonExists('Remove');
    $this->getSession()->getPage()->pressButton('Remove');
    $this->waitForAjaxToFinish();
    $this->assertSession()->pageTextNotContains('llama.jpg');
    $this->assertSession()->linkExists('Select entities');
  }

  /**
   * Tests tabs widget selector.
   */
  public function testTabsWidgetSelector() {

    // Sets the tabs widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setWidgetSelector('tabs');
    $browser->save();

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'tabs', 'Widget selector is set to tabs.');

    // Create a file.
    $image = $this->createFile('llama');

    // Create a second file.
    $image2 = $this->createFile('llama2');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->assertSession()->linkExists('dummy');
    $this->assertSession()->linkExists('view');
    $this->assertSession()->linkExists('upload');

    $this->assertEquals('is-active active', $this->getSession()->getPage()->findLink('view')->getAttribute('class'));

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');
    $this->getSession()->switchToIFrame();

    $this->waitForAjaxToFinish();

    $this->assertSession()->pageTextContains('llama.jpg');

    $this->getSession()->getPage()->clickLink('Select entities');
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');
    $this->getSession()->getPage()->clickLink('upload');

    // This is producing an error. Still investigating
    // InvalidStateError: DOM Exception 11: An attempt was made to use an object
    // that is not, or is no longer, usable.
    //$edit = [
    //  'files[upload][]' => $this->container->get('file_system')->realpath($image2->getFileUri()),
    //];
    // $this->drupalPostForm(NULL, $edit, 'Select files');.

    \Drupal::state()->set('eb_test_dummy_widget_access', FALSE);
    $this->drupalGet('entity-browser/iframe/test_entity_browser_file');
    $this->assertSession()->linkNotExists('dummy');
    $this->assertSession()->linkExists('view');
    $this->assertSession()->linkExists('upload');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Contexts', 'eb_dummy');

    // Move dummy widget to the first place and make sure it does not appear.
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file');
    $browser->getWidget('cbc59500-04ab-4395-b063-c561f0e3bf80')->setWeight(-15);
    $browser->save();
    $this->drupalGet('entity-browser/iframe/test_entity_browser_file');
    $this->assertSession()->linkNotExists('dummy');
    $this->assertSession()->linkExists('view');
    $this->assertSession()->linkExists('upload');
    $this->assertSession()->pageTextNotContains('This is dummy widget.');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Contexts', 'eb_dummy');
  }

  /**
   * Tests dropdown widget selector.
   */
  public function testDropdownWidgetSelector() {

    // Sets the dropdown widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setWidgetSelector('drop_down');
    $browser->save();

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'drop_down', 'Widget selector is set to dropdown.');

    // Create a file.
    $image = $this->createFile('llama');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->assertSession()->selectExists('widget');
    $this->assertSession()->optionExists('widget', 'cbc59500-04ab-4395-b063-c561f0e3bf80'); // Dummy
    $this->assertSession()->optionExists('widget', '2dc1ab07-2f8f-42c9-aab7-7eef7f8b7d87'); // Upload
    $this->assertSession()->optionExists('widget', '774798f1-5ec5-4b63-84bd-124cd51ec07d'); // View
    // Selects the view widget.
    $this->getSession()->getPage()->selectFieldOption('widget', '774798f1-5ec5-4b63-84bd-124cd51ec07d');

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');
    $this->getSession()->switchToIFrame();

    $this->waitForAjaxToFinish();

    $this->assertSession()->pageTextContains('llama.jpg');

    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    // Causes a fatal.
    // Selects the upload widget.
    // $this->getSession()->getPage()->selectFieldOption('widget', '2dc1ab07-2f8f-42c9-aab7-7eef7f8b7d87');.

    \Drupal::state()->set('eb_test_dummy_widget_access', FALSE);
    $this->drupalGet('entity-browser/iframe/test_entity_browser_file');
    $this->assertSession()->optionNotExists('widget', 'cbc59500-04ab-4395-b063-c561f0e3bf80'); // Dummy
    $this->assertSession()->optionExists('widget', '2dc1ab07-2f8f-42c9-aab7-7eef7f8b7d87'); // Upload
    $this->assertSession()->optionExists('widget', '774798f1-5ec5-4b63-84bd-124cd51ec07d'); // View
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Contexts', 'eb_dummy');

    // Move dummy widget to the first place and make sure it does not appear.
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file');
    $browser->getWidget('cbc59500-04ab-4395-b063-c561f0e3bf80')->setWeight(-15);
    $browser->save();
    $this->drupalGet('entity-browser/iframe/test_entity_browser_file');
    $this->assertSession()->optionNotExists('widget', 'cbc59500-04ab-4395-b063-c561f0e3bf80'); // Dummy
    $this->assertSession()->optionExists('widget', '2dc1ab07-2f8f-42c9-aab7-7eef7f8b7d87'); // Upload
    $this->assertSession()->optionExists('widget', '774798f1-5ec5-4b63-84bd-124cd51ec07d'); // View
    $this->assertSession()->pageTextNotContains('This is dummy widget.');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Contexts', 'eb_dummy');
  }

  /**
   * Tests views selection display.
   */
  public function testViewsSelectionDisplayWidget() {

    // Sets the dropdown widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setSelectionDisplay('view');
    $browser->save();

    $this->assertEquals($browser->getSelectionDisplay()->getPluginId(), 'view', 'Selection display is set to view.');

  }

  /**
   * Tests NoDisplay selection display plugin.
   */
  public function testNoDisplaySelectionDisplay() {
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('node.article.default');

    $form_display->setComponent('field_reference', [
      'type' => 'entity_browser_entity_reference',
      'settings' => [
        'entity_browser' => 'multiple_submit_example',
        'field_widget_display' => 'label',
        'open' => TRUE,
      ],
    ])->save();

    $account = $this->drupalCreateUser([
      'access multiple_submit_example entity browser pages',
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('node/add/article');
    // Open the entity browser widget form.
    $this->getSession()->getPage()->clickLink('Select entities');
    $this->getSession()->switchToIFrame('entity_browser_iframe_multiple_submit_example');

    // Click the second submit button to make sure the widget does not close.
    $this->getSession()->getPage()->pressButton('Second submit button');

    // Check that the entity browser widget is still open.
    $this->getSession()->getPage()->hasButton('Second submit button');

    // Click the primary submit button to close the widget.
    $this->getSession()->getPage()->pressButton('Select entities');

    // Check that the entity browser widget is closed.
    $this->assertSession()->buttonNotExists('Second submit button');
  }

}
