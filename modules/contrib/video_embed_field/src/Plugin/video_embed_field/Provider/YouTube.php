<?php

namespace Drupal\video_embed_field\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;

/**
 * A YouTube provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "youtube",
 *   title = @Translation("YouTube")
 * )
 */
class YouTube extends ProviderPluginBase {

  const DEFAULT_EMBED_URL = 'https://www.youtube.com/embed/%s';
  const PRIVACY_EMBED_URL = 'https://www.youtube-nocookie.com/embed/%s';

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    $setting = $this->getConfig()->get('privacy_mode');
    switch ($setting) {
      case 'enabled':
        $url = YouTube::PRIVACY_EMBED_URL;
        break;

      case 'optional':
        // All domains like youtu.be redirect in the normal embed url so unless
        // the user's input contains the youtube-nocookie domain, use the
        // default url.
        preg_match('#^(?:https?://|//)?(?:www\.)?(?<nocookie>youtube\-nocookie\.com)/(?:/.*)?#i', $this->getInput(), $matches);
        $url = isset($matches['nocookie']) ? YouTube::PRIVACY_EMBED_URL : YouTube::DEFAULT_EMBED_URL;
        break;

      case 'disabled':
      default:
        $url = YouTube::DEFAULT_EMBED_URL;
        break;

    }

    $embed_code = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'youtube',
      '#url' => sprintf($url, $this->getVideoId()),
      '#query' => [
        'autoplay' => $autoplay,
        'start' => $this->getTimeIndex(),
        'rel' => '0',
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
    if ($language = $this->getLanguagePreference()) {
      $embed_code['#query']['cc_lang_pref'] = $language;
    }

    $embed_code['#cache']['tags'][] = 'config:video_embed_field.settings';

    return $embed_code;
  }

  /**
   * Get the time index for when the given video starts.
   *
   * @return int
   *   The time index where the video should start based on the URL.
   */
  protected function getTimeIndex() {
    preg_match('/[&\?]t=((?<hours>\d+)h)?((?<minutes>\d+)m)?(?<seconds>\d+)s?/', $this->getInput(), $matches);

    $hours = !empty($matches['hours']) ? $matches['hours'] : 0;
    $minutes = !empty($matches['minutes']) ? $matches['minutes'] : 0;
    $seconds = !empty($matches['seconds']) ? $matches['seconds'] : 0;

    return $hours * 3600 + $minutes * 60 + $seconds;
  }

  /**
   * Extract the language preference from the URL for use in closed captioning.
   *
   * @return string|FALSE
   *   The language preference if one exists or FALSE if one could not be found.
   */
  protected function getLanguagePreference() {
    preg_match('/[&\?]hl=(?<language>[a-z\-]*)/', $this->getInput(), $matches);
    return isset($matches['language']) ? $matches['language'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    $url = 'http://img.youtube.com/vi/%s/%s.jpg';
    $high_resolution = sprintf($url, $this->getVideoId(), 'maxresdefault');
    $backup = sprintf($url, $this->getVideoId(), 'mqdefault');
    try {
      $this->httpClient->head($high_resolution);
      return $high_resolution;
    }
    catch (\Exception $e) {
      return $backup;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    // Supports protocols "http://", "https://" or "//".
    $prefix = '(?:https?://|//)?';

    $url_regexes = [
      // youtu.be receives the id as the second parameter.
      $prefix . '?youtu\.be/(?<id>[^\#\&\?]+)(?:/.*)?',
      // Default youtube url that scans for the id in the v query parameter.
      // Supports both www and m subdomains.
      $prefix . '(?!.*list=)(?:www\.|m\.)?youtube\.com/(?:(?:watch)?)\?(?:.*&)?v=(?<id>[^\#\&\?]+)(?:/.*)?',
      // Embed url. The id comes after the 'embed' keyword.
      $prefix . '(?!.*list=)(?:www\.)?youtube\.com/embed/(?<id>[^\#\&\?]+)(?:/.*)?',
      // No cookie domain. Only supports embedded urls. Does not support the m
      // subdomain.
      $prefix . '(?:www\.)?youtube\-nocookie\.com/embed/(?<id>[^\#\&\?]+)(?:/.*)?',
    ];

    foreach ($url_regexes as $regex) {
      preg_match('#^' . $regex . '#i', $input, $matches);
      if (isset($matches['id'])) {
        return $matches['id'];
      }
    }

    return FALSE;
  }

}
