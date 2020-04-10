<?php

namespace Drupal\entity_print;

use Drupal\Component\Transliteration\TransliterationInterface;

/**
 * A service for generating filenames for printed documents.
 */
class FilenameGenerator implements FilenameGeneratorInterface {

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * FilenameGenerator constructor.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   Transliteration service.
   */
  public function __construct(TransliterationInterface $transliteration) {
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public function generateFilename(array $entities, callable $entity_label_callback = NULL) {
    $filenames = [];
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    foreach ($entities as $entity) {
      if ($label = trim($this->sanitizeFilename($entity_label_callback ? $entity_label_callback($entity) : $entity->label(), $entity->language()->getId()))) {
        $filenames[] = $label;
      }
    }

    return $filenames ? implode('-', $filenames) : static::DEFAULT_FILENAME;
  }

  /**
   * Gets a safe filename.
   *
   * @param string $filename
   *   The un-processed filename.
   * @param string $langcode
   *   The language of the filename.
   *
   * @return string
   *   The filename stripped to only safe characters.
   */
  protected function sanitizeFilename($filename, $langcode) {
    $transformed = $this->transliteration->transliterate($filename, $langcode);
    return preg_replace("/[^A-Za-z0-9 ]/", '', $transformed);
  }

}
