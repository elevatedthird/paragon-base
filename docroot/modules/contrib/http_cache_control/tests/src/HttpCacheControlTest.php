<?php

namespace Drupal\Tests\http_cache_control;

use Drupal\Tests\BrowserTestBase;

/**
 * Enables the page cache and tests it with various HTTP requests.
 *
 * @group http_cache_control
 */
class HttpCacheControlTest extends BrowserTestBase {

  protected $dumpHeaders = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['test_page_test', 'system_test', 'http_cache_control'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.site')
      ->set('name', 'Drupal')
      ->set('page.front', '/test-page')
      ->save();
  }

  /**
   * Tests cache headers.
   */
  public function testPageCache() {
    $config = $this->config('system.performance');
    $config->set('cache.page.max_age', 300);
    $config->save();

    // Fill the cache.
    $this->drupalGet('system-test/set-header', ['query' => ['name' => 'Foo', 'value' => 'max-age']]);

    $this->assertEquals($this->getSession()->getResponseHeader('Cache-Control'), 'max-age=300, public', 'Cache-Control header was sent.');
    $this->assertStringNotContainsString('s-maxage', $this->getSession()->getResponseHeader('Cache-Control'), 'Cache-Control header does not contain s-maxage');
    $this->assertEmpty($this->getSession()->getResponseHeader('Surrogate-Control'), 'Surrogate-Control is not present');

    $config = $this->config('http_cache_control.settings');
    $config->set('cache.http.s_maxage', 400);
    $config->save();

    $this->drupalGet('system-test/set-header', ['query' => ['name' => 'Foo', 'value' => 's-maxage']]);
    $this->assertStringContainsString('s-maxage=400', $this->getSession()->getResponseHeader('Cache-Control'), 'Cache-Control header contain s-maxage');

    $config->set('cache.http.404_max_age', 404);
    $config->save();

    $this->drupalGet('system-test/not-found');
    $this->assertStringContainsString('max-age=404', $this->getSession()->getResponseHeader('Cache-Control'), 'Cache-Control header contain maxage for 404');
    $this->assertStringContainsString('s-maxage=404', $this->getSession()->getResponseHeader('Cache-Control'), 'Cache-Control header does not contain s-maxage');

    $config->set('cache.http.vary', 'Drupal-Test-Header');
    $config->save();

    $this->drupalGet('system-test/set-header', ['query' => ['name' => 'Foo', 'value' => 'vary']]);
    $this->assertStringContainsString('Drupal-Test-Header', $this->getSession()->getResponseHeader('Vary'), 'Vary header contains Drupal-Test-Header.');

    // Surrogate Control tests.
    $config->set('cache.surrogate.maxage', 405);
    $config->save();

    $this->drupalGet('system-test/set-header', ['query' => ['name' => 'Foo', 'value' => 'surrogate-max-age']]);
    $this->assertStringContainsString('max-age=405', $this->getSession()->getResponseHeader('Surrogate-Control'), 'Surrogate-Control header contains maxage');
    $this->assertStringNotContainsString('no-store', $this->getSession()->getResponseHeader('Surrogate-Control'), 'Surrogate-Control header does not contain no-store');

    $config->set('cache.surrogate.nostore', TRUE);
    $config->save();

    $this->drupalGet('system-test/set-header', ['query' => ['name' => 'Foo', 'value' => 'surrogate-nostore']]);
    $this->assertStringContainsString('max-age=405', $this->getSession()->getResponseHeader('Surrogate-Control'), 'Surrogate-Control header contains maxage');
    $this->assertStringContainsString('no-store', $this->getSession()->getResponseHeader('Surrogate-Control'), 'Surrogate-Control header does contain no-store');
  }

}
