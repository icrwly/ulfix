<?php
/**
 * Load services definition file.
 */
$settings["container_yamls"][] = __DIR__ . "/services.yml";

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

$settings["update_free_access"] = false;

$settings["entity_update_batch_size"] = 50;

$is_pantheon_env = isset($_ENV["PANTHEON_ENVIRONMENT"]);
$pantheon_env = $is_pantheon_env ? $_ENV["PANTHEON_ENVIRONMENT"] : null;
//echo "Pantheon Environment: " . $pantheon_env;
$is_pantheon_dev_env = $pantheon_env == "dev" || str_contains($pantheon_env, "ci-") || str_contains($pantheon_env, "pr-") || str_contains($pantheon_env, "develop");
$is_pantheon_stage_env = $pantheon_env == "test";
$is_pantheon_prod_env = $pantheon_env == "live";
$is_local_env = $pantheon_env == "lando";
$is_site_name = isset($_ENV["PANTHEON_SITE_NAME"]);
$site_name = $is_site_name ? $_ENV["PANTHEON_SITE_NAME"] : null;
//echo "UL Site: " . $site_name;
//$settings["config_sync_directory"] = dirname(DRUPAL_ROOT) . "/config/default";
if ($is_site_name)
{
    if ($site_name == 'hubul')
    {
        //      echo "using ul_guidelines_profile";
        $settings["config_sync_directory"] = dirname(DRUPAL_ROOT) . "/config/ul_guidelines_profile/default";
    }
    else
    {
        //    echo "using ul_enterprise_profile";
        $settings["config_sync_directory"] = dirname(DRUPAL_ROOT) . "/config/ul_enterprise_profile/default";
    }
}

// Default environment splits to false.
$config["config_split.config_split.local"]["status"] = false;
$config["config_split.config_split.ah_other"]["status"] = false;
$config["config_split.config_split.dev"]["status"] = false;
$config["config_split.config_split.stage"]["status"] = false;
$config["config_split.config_split.prod"]["status"] = false;

// Default site splits to false. 
$config["config_split.config_split.aunz"]["status"] = false;
$config["config_split.config_split.brandhubstage"]["status"] = false;
$config["config_split.config_split.emergo"]["status"] = false;
$config["config_split.config_split.latam"]["status"] = false;
$config["config_split.config_split.news-canada"]["status"] = false;
$config["config_split.config_split.publish"]["status"] = false;
$config["config_split.config_split.SamlAuth"]["status"] = false;
$config["config_split.config_split.shimadzu"]["status"] = false;
$config["config_split.config_split.ulmvp"]["status"] = false;

if ($is_site_name)
{
    switch ($site_name)
    {
        case "aunzul":
            $config["config_split.config_split.aunz"]["status"] = true;
            break;
        case "hubul":
            $config["config_split.config_split.brandhubstage"]["status"] = true;
            break;
        case "emergo1":
            $config["config_split.config_split.emergo"]["status"] = true;
            break;
        case "latamul":
            $config["config_split.config_split.latam"]["status"] = true;
            break;
        case "shimadzu":
            $config["config_split.config_split.shimadzu"]["status"] = true;
            break;
        case "wwwul":
            $config["config_split.config_split.ulmvp"]["status"] = true;
            break;
    }
}

/**
 * Environment Specific Settings.
 *
 */
 
 if ($is_pantheon_env) {
    switch ($pantheon_env) {
        case $is_pantheon_stage_env:
            $config["config_split.config_split.stage"]["status"] = true;
            break;
        case $is_pantheon_prod_env:
            $config["config_split.config_split.prod"]["status"] = true;
            break;
        case $is_pantheon_dev_env:
            $config["config_split.config_split.dev"]["status"] = true;
            break;
        default:
            $config["config_split.config_split.prod"]["status"] = true;
            break;
    }
  }
  
$settings["hash_salt"] = file_get_contents(DRUPAL_ROOT . "/../salt.txt");

// $config["system.logging"]["error_level"] = "verbose";
// error_reporting(E_ALL);
// ini_set("display_errors", true);
// ini_set("display_startup_errors", true);

/**
 * Deployment identifier.
 *
 * Drupal's dependency injection container will be automatically invalidated and
 * rebuilt when the Drupal core version changes. When updating contributed or
 * custom code that changes the container, changing this identifier will also
 * allow the container to be invalidated as soon as code is deployed.
 */
$settings["deployment_identifier"] = \Drupal::VERSION;
$deploy_id_file = DRUPAL_ROOT . "/../deployment_identifier";
if (file_exists($deploy_id_file))
{
    $settings["deployment_identifier"] = file_get_contents($deploy_id_file);
}

// Sets redirection for old file paths from Acquia /sites/g/files/dprerj…/files/ to /sites/default/files directory.
if (php_sapi_name() != "cli")
{
    $pattern = "/\/sites\/g\/files\/dprerj([A-Za-z0-9_\.]+)?\/(f|files)\//i";
    if (($redirect = preg_replace($pattern, "/sites/default/files/", $_SERVER["REQUEST_URI"])) && $redirect != $_SERVER["REQUEST_URI"])
    {
        header("HTTP/1.0 301 Moved Permanently");
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $redirect);

        // Name transaction "redirect" in New Relic for improved reporting (optional).
        if (extension_loaded("newrelic"))
        {
            newrelic_name_transaction("redirect");
        }

        exit();
    }
}

// Provide universal absolute path to the installation.
if (isset($_ENV["PANTHEON_ENVIRONMENT"]) && is_dir("/code/vendor/simplesamlphp/simplesamlphp"))
{
    $settings["simplesamlphp_dir"] = "/code/vendor/simplesamlphp/simplesamlphp";

    // Force server port to 443 with HTTPS environments when behind a load
    // balancer which is a requirement for SimpleSAML when providing a
    // redirect path.
    // @see https://github.com/simplesamlphp/simplesamlphp/issues/450
    if (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === "on")
    {
        $_SERVER["SERVER_PORT"] = 443;
    }
}
else
{
    // Local SAML path.
    if (is_dir(DRUPAL_ROOT . "/../simplesamlphp") && is_dir(DRUPAL_ROOT . "/../vendor/simplesamlphp/simplesamlphp"))
    {
        $settings["simplesamlphp_dir"] = DRUPAL_ROOT . "/../vendor/simplesamlphp/simplesamlphp";
    }
}

// Configure Redis

if (defined(
 'PANTHEON_ENVIRONMENT'
) && !\Drupal\Core\Installer\InstallerKernel::installationAttempted(
) && extension_loaded('redis')) {
 // Set Redis as the default backend for any cache bin not otherwise specified.
 $settings['cache']['default'] = 'cache.backend.redis';

 //phpredis is built into the Pantheon application container.
 $settings['redis.connection']['interface'] = 'PhpRedis';

 // These are dynamic variables handled by Pantheon.
 $settings['redis.connection']['host'] = $_ENV['CACHE_HOST'];
 $settings['redis.connection']['port'] = $_ENV['CACHE_PORT'];
 $settings['redis.connection']['password'] = $_ENV['CACHE_PASSWORD'];

 $settings['redis_compress_length'] = 100;
 $settings['redis_compress_level'] = 1;

 $settings['cache_prefix']['default'] = 'pantheon-redis';

 $settings['cache']['bins']['form'] = 'cache.backend.database'; // Use the database for forms

 // Apply changes to the container configuration to make better use of Redis.
 // This includes using Redis for the lock and flood control systems, as well
 // as the cache tag checksum. Alternatively, copy the contents of that file
 // to your project-specific services.yml file, modify as appropriate, and
 // remove this line.
 $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

 // Allow the services to work before the Redis module itself is enabled.
 $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

 // Manually add the classloader path, this is required for the container
 // cache bin definition below.
 $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

 // Use redis for container cache.
 // The container cache is used to load the container definition itself, and
 // thus any configuration stored in the container itself is not available
 // yet. These lines force the container cache to use Redis rather than the
 // default SQL cache.
 $settings['bootstrap_container_definition'] = [
   'parameters' => [],
   'services' => [
     'redis.factory' => [
       'class' => 'Drupal\redis\ClientFactory',
     ],
     'cache.backend.redis' => [
       'class' => 'Drupal\redis\Cache\CacheBackendFactory',
       'arguments' => [
         '@redis.factory',
         '@cache_tags_provider.container',
         '@serialization.phpserialize',
       ],
     ],
     'cache.container' => [
       'class' => '\Drupal\redis\Cache\PhpRedis',
       'factory' => ['@cache.backend.redis', 'get'],
       'arguments' => ['container'],
     ],
     'cache_tags_provider.container' => [
       'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
       'arguments' => ['@redis.factory'],
     ],
     'serialization.phpserialize' => [
       'class' => 'Drupal\Component\Serialization\PhpSerialize',
     ],
   ],
 ];
}
/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings))
{
    include $local_settings;
}

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . "/settings.ddev.php";
if (getenv("IS_DDEV_PROJECT") == "true" && is_readable($ddev_settings))
{
    require $ddev_settings;
    $config["config_split.config_split.local"]["status"] = true;

    // Manually set which site you're simulating, until we find an automatic way.
    $local_site_split = 'ulmvp';

    $config["config_split.config_split." . $local_site_split]["status"] = true;

    $settings["config_sync_directory"] = dirname(DRUPAL_ROOT) . "/config/ul_enterprise_profile/default";

    if ($local_site_split == 'brandhubstage') {
        $settings["config_sync_directory"] = dirname(DRUPAL_ROOT) . "/config/ul_guidelines_profile/default";
    }
    
}
