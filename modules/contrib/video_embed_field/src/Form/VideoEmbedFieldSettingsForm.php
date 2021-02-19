<?php

namespace Drupal\video_embed_field\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class VideoEmbedFieldSettingsForm.
 */
class VideoEmbedFieldSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['video_embed_field.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'video_embed_field_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['privacy_mode'] = [
      '#title' => $this->t('Privacy mode'),
      '#description' => t('Some providers might offer an option for '
        . ' enhanced privacy settings (e.g. Youtube offers the youtube-nocookie domain. The following options are allowed:'
        . '<br />Enabled: The settings will be enforced for all embeded URLs that support it.'
        . '<br />Optional: The settings will depend on the user\'s input.'
        . '<br />Disabled: Privacy options are not supported.'
      ),
      '#type' => 'select',
      '#options' => [
        'enabled' => $this->t('Enabled'),
        'optional' => $this->t('Optional'),
        'disabled' => $this->t('Disabled'),
      ],
      '#default_value' => $this->config('video_embed_field.settings')->get('privacy_mode'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('video_embed_field.settings')
      ->set('privacy_mode', $values['privacy_mode'])
      ->save();

    Cache::invalidateTags(['config:video_embed_field.settings']);
    parent::submitForm($form, $form_state);
  }

}
