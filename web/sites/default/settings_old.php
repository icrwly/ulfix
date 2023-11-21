<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Override pantheon's default config_sync_directory.
 */
$settings['config_sync_directory'] = dirname(DRUPAL_ROOT) . '/config/default';

/**
 * Increase memory limit for drush / cli commands.
 */

if (PHP_SAPI === 'cli') {
  ini_set('memory_limit', '512M');
}

$config['system.logging']['error_level'] = 'verbose';

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Useful variables
 */
$repo_root = dirname(DRUPAL_ROOT);

/**
 * Dynamic Pantheon envs settings allows for testing release workflows in any multidev or dev env.
 */

$is_pantheon_env = isset($_ENV['PANTHEON_ENVIRONMENT']);
$pantheon_env = $is_pantheon_env ? $_ENV['PANTHEON_ENVIRONMENT'] : NULL;
$is_pantheon_dev_env = $pantheon_env == 'dev' || str_contains($pantheon_env, 'ci-') || str_contains($pantheon_env, 'pr-') || str_contains($pantheon_env, 'develop');
$is_pantheon_stage_env = $pantheon_env == 'test';
$is_pantheon_prod_env = $pantheon_env == 'live';
$is_local_env = $pantheon_env == 'lando';

/**
 * Environment Specific Settings.
 *
 */
if ( $is_pantheon_env) {
	switch( $pantheon_env ) {
    case 'test':
      $config['config_split.config_split.stage']['status'] = TRUE;
      // Additional test env config/settings.
      break;
    case 'live':
      $config['config_split.config_split.prod']['status'] = TRUE;
      // Additional live config/settings.
      break;
    case 'dev':
     // Additional dev env config/settings.
      $config['config_split.config_split.dev']['status'] = TRUE;
      break;
    case 'lando':
      // Enable local modules.
      $config['config_split.config_split.local']['status'] = TRUE;
      break;
    default:
      $config['config_split.config_split.dev']['status'] = TRUE;
      break;
  }
}

/**
 * If there is a redis settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}


    /**
     * Redis settings - these are enhancements to the default Pantheon Redis settings
     * found on https://pantheon.io/docs/object-cache#enable-object-cache.
     *
     */

     if (defined('PANTHEON_ENVIRONMENT') && !\Drupal\Core\Installer\InstallerKernel::installationAttempted() && extension_loaded('redis')) {

      // Set Redis as the default backend for any cache bin not otherwise specified.
      $settings['cache']['default'] = 'cache.backend.redis';
    
      //phpredis is built into the Pantheon application container.
      $settings['redis.connection']['interface'] = 'PhpRedis';
    
      // These are dynamic variables handled by Pantheon.
      $settings['redis.connection']['host']      = $_ENV['CACHE_HOST'];
      $settings['redis.connection']['port']      = $_ENV['CACHE_PORT'];
      $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];
      
      // This overrides lock and flood.
      $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';
      $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';
      $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');
    
    /**
     * Default TTL for Redis is 1 year.
     *
     * Change cache expiration (TTL) for data,default bin - 12 hours.
     * Change cache expiration (TTL) for entity bin - 48 hours.
     *
     * @see \Drupal\redis\Cache\CacheBase::LIFETIME_PERM_DEFAULT
     */
    
      $settings['redis.settings']['perm_ttl'] = 2630000; // 30 days
      $settings['redis.settings']['perm_ttl_config'] = 43200;
      $settings['redis.settings']['perm_ttl_data'] = 43200;
      $settings['redis.settings']['perm_ttl_default'] = 43200;
      $settings['redis.settings']['perm_ttl_entity'] = 172800;
    
      $settings['cache']['default'] = 'cache.backend.redis';
      $settings['cache_prefix']['default'] = 'pantheon-redis-json';
      $settings['cache']['bins']['form'] = 'cache.backend.database';
    
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'redis.factory' => [
          'class' => 'Drupal\redis\ClientFactory',
        ],
        'cache.backend.redis' => [
          'class' => 'Drupal\redis\Cache\CacheBackendFactory',
          'arguments' => ['@redis.factory', '@cache_tags_provider.container', '@serialization.json'],
        ],
        'cache.container' => [
          'class' => 'Drupal\redis\Cache\PhpRedis',
          'factory' => ['@cache.backend.redis', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
          'arguments' => ['@redis.factory'],
        ],
        'serialization.json' => [
          'class' => 'Drupal\Component\Serialization\Json',
        ],
      ],
    ];
    
    }













