<?php
# ================================================================
# In development, I prefer to have all errors displayed
# ================================================================
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

# ================================================================
# Enable local development services.
# ================================================================
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yaml';

# ================================================================
# disable page cache and other cache bins
# ================================================================

$cache_bins = array('bootstrap','config','data','default','discovery','dynamic_page_cache','entity','menu','migrate','render','rest','static','toolbar');
foreach ($cache_bins as $bin) {
  $settings['cache']['bins'][$bin] = 'cache.backend.null';
}

# ================================================================
# disable advagg module settings
# ================================================================
$config['advagg.settings']['enabled'] = FALSE;

# ================================================================
# dynamic build values
# ================================================================
# $base_url = 'https://dev.site.it';

# ================================================================
# performance
# ================================================================
$config['system.logging']['error_level'] = 'verbose';
$config['system.performance']['cache']['page']['use_internal'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = FALSE;

# ================================================================
# emit views debug data
# ================================================================
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;

# ================================================================
# expiration of temporary upload files
# ================================================================
$config['system.file']['temporary_maximum_age'] = 604800;
