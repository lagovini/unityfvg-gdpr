<?php

namespace Drupal\entity_print\Plugin\EntityPrint\PrintEngine;

use Dompdf\Dompdf as DompdfLib;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_print\Plugin\ExportTypeInterface;
use Drupal\entity_print\PrintEngineException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Adapter\CPDF;

/**
 * A Entity Print plugin for the DomPdf library.
 *
 * @PrintEngine(
 *   id = "dompdf",
 *   label = @Translation("Dompdf"),
 *   export_type = "pdf"
 * )
 */
class DomPdf extends PdfEngineBase implements ContainerFactoryPluginInterface {

  /**
   * Name of DomPdf log file.
   *
   * @var string
   */
  const LOG_FILE_NAME = 'log.html';

  /**
   * The Dompdf instance.
   *
   * @var \Dompdf\Dompdf
   */
  protected $dompdf;

  /**
   * Keep track of HTML pages as they're added.
   *
   * @var string
   */
  protected $html = '';

  /**
   * Keep track of whether we've rendered or not.
   *
   * @var bool
   */
  protected $hasRendered;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExportTypeInterface $export_type, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $export_type);
    $this->dompdf = new DompdfLib($this->configuration);
    $this->dompdf->setPaper($this->configuration['default_paper_size'], $this->configuration['orientation']);
    $this->dompdf->set_option('temp_dir', file_directory_temp());
    $this->dompdf->set_option('log_output_file', file_directory_temp() . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME);
    if ($this->configuration['disable_log']) {
      $this->dompdf->set_option('log_output_file', '');
    }
    $this->dompdf
      ->setBaseHost($request->getHttpHost())
      ->setProtocol($request->getScheme() . '://');

    $this->setupHttpContext();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.entity_print.export_type')->createInstance($plugin_definition['export_type']),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getInstallationInstructions() {
    return t('Please install with: @command', ['@command' => 'composer require "dompdf/dompdf 0.8.0"']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'enable_html5_parser' => TRUE,
      'disable_log' => FALSE,
      'enable_remote' => TRUE,
      'cafile' => '',
      'verify_peer' => TRUE,
      'verify_peer_name' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['enable_html5_parser'] = [
      '#title' => $this->t('Enable HTML5 Parser'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['enable_html5_parser'],
      '#description' => $this->t("Note, this library doesn't work without this option enabled."),
    ];
    $form['disable_log'] = [
      '#title' => $this->t('Disable Log'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['disable_log'],
      '#description' => $this->t("Check to disable DomPdf logging to <code>@log_file_name</code> in Drupal's temporary directory.", [
        '@log_file_name' => self::LOG_FILE_NAME,
      ]),
    ];
    $form['enable_remote'] = [
      '#title' => $this->t('Enable Remote URLs'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['enable_remote'],
      '#description' => $this->t('This settings must be enabled for CSS and Images to work unless you manipulate the source manually.'),
    ];
    $form['ssl_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('SSL Configuration'),
      '#open' => !empty($this->configuration['cafile']) || empty($this->configuration['verify_peer']) || empty($this->configuration['verify_peer_name']),
    ];
    $form['ssl_configuration']['cafile'] = [
      '#title' => $this->t('CA File'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['cafile'],
      '#description' => $this->t('Path to the CA file. This may be needed for development boxes that use SSL. You can leave this empty in production.'),
    ];
    $form['ssl_configuration']['verify_peer'] = [
      '#title' => $this->t('Verify Peer'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['verify_peer'],
      '#description' => $this->t("Verify an SSL Peer's certificate. For development only, do not disable this in production. See https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html"),
    ];
    $form['ssl_configuration']['verify_peer_name'] = [
      '#title' => $this->t('Verify Peer Name'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['verify_peer_name'],
      '#description' => $this->t("Verify an SSL Peer's certificate. For development only, do not disable this in production. See https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html"),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($content) {
    // We must keep adding to previously added HTML as loadHtml() replaces the
    // entire document.
    $this->html .= (string) $content;
    $this->dompdf->loadHtml($this->html);
  }

  /**
   * {@inheritdoc}
   */
  public function send($filename, $force_download = TRUE) {
    $this->doRender();

    // Dompdf doesn't have a return value for send so just check the error
    // global it provides.
    if ($errors = $this->getError()) {
      throw new PrintEngineException(sprintf('Failed to generate PDF: %s', $errors));
    }

    // The Dompdf library internally adds the .pdf extension so we remove it
    // from our filename here.
    $filename = preg_replace('/\.pdf$/i', '', $filename);

    // If the filename received here is NULL, force open in the browser
    // otherwise attempt to have it downloaded.
    $this->dompdf->stream($filename, ['Attachment' => $force_download]);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlob() {
    $this->doRender();
    return $this->dompdf->output();
  }

  /**
   * Tell Dompdf to render the HTML into a PDF.
   */
  protected function doRender() {
    if (!$this->hasRendered) {
      $this->dompdf->render();
      $this->hasRendered = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getError() {
    global $_dompdf_warnings;
    if (is_array($_dompdf_warnings)) {
      return implode(', ', $_dompdf_warnings);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function dependenciesAvailable() {
    return class_exists('Dompdf\Dompdf') && !drupal_valid_test_ua();
  }

  /**
   * Setup the HTTP Context used by Dompdf for requesting resources.
   */
  protected function setupHttpContext() {
    $context_options = [
      'ssl' => [
        'verify_peer' => $this->configuration['verify_peer'],
        'verify_peer_name' => $this->configuration['verify_peer_name'],
      ],
    ];
    if ($this->configuration['cafile']) {
      $context_options['ssl']['cafile'] = $this->configuration['cafile'];
    }

    // If we have authentication then add it to the request context.
    if (!empty($this->configuration['username'])) {
      $auth = base64_encode(sprintf('%s:%s', $this->configuration['username'], $this->configuration['password']));
      $context_options['http']['header'] = [
        'Authorization: Basic ' . $auth,
      ];
    }

    $http_context = stream_context_create($context_options);
    $this->dompdf->setHttpContext($http_context);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPaperSizes() {
    return array_combine(array_keys(CPDF::$PAPER_SIZES), array_map('ucfirst', array_keys(CPDF::$PAPER_SIZES)));
  }

  /**
   * {@inheritdoc}
   */
  public function getPrintObject() {
    return $this->dompdf;
  }

}
