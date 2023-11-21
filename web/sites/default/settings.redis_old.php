<?php

/**
 * High performance Redis settings - these are enhancements to the default Pantheon Redis settings
* found on https://pantheon.io/docs/object-cache#enable-object-cache.
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
    $settings['container_yamls'][] = 'modules/composer/redis/example.services.yml';
    $settings['container_yamls'][] = 'modules/composer/redis/redis.services.yml';
    $class_loader->addPsr4('Drupal\\redis\\', 'modules/composer/redis/src');
  
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













