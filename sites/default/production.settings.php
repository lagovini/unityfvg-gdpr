<?php
# ================================================================
# dynamic build values
# ================================================================
# $base_url = 'https://site.it';

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/production.services.yaml';

# ================================================================
# performance
# ================================================================
$config['system.logging']['error_level'] = 'hide';
$config['system.performance']['cache']['page']['use_internal'] = TRUE;
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;

# ================================================================
# emit views debug data
# ================================================================
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;

# ================================================================
# expiration of temporary upload files
# ================================================================
$config['system.file']['temporary_maximum_age'] = 31536000;
