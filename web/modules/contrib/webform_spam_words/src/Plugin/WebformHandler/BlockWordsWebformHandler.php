<?php

namespace Drupal\webform_spam_words\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\Component\Utility\Html;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "webform_spam_words",
 *   label = @Translation("Webform Spam Words"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Webform spam words to validate it based on spam keywords."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class BlockWordsWebformHandler extends WebformHandlerBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    // Get values from Webform spam words settings.
    $wsw_settings = \Drupal::config('webform_spam_words.settings');
    $spam_words = $wsw_settings->get('spam_words', 'SEO');
    $spam_words_error = $wsw_settings->get('spam_text_message', 'Unable to submit form. Please contact the site administrator, if the problem persists.');
    $spam_field = $wsw_settings->get('spam_field_name', 'message');

    $fields = explode(',', trim($spam_field));
    if (is_array($fields)) {
      foreach ($fields as $value) {
        $field = trim($value);
        $this->validateSpam($form_state, $field, $spam_words, $spam_words_error);
      }
    }

  }

  /**
   * Validate Webform spam words.
   */
  private function validateSpam(FormStateInterface $formState, $field, $spam_words, $spam_words_error) {

    $spam_error = FALSE;
    $field_value = $formState->getValue($field);
    $field_value = (is_array($field_value) && isset($field_value['value'])) ? $field_value['value'] : $field_value;
    $value = (!empty($field_value) && !is_array($field_value)) ? Html::escape(mb_strtolower($field_value)) : NULL;

    // Skip empty unique fields or arrays.
    if (empty($value) || is_array($value)) {
      return;
    }

    // Remove whitespaces in between strings.
    $value = preg_replace('/\s+/', ' ', $value);

    foreach ($spam_words as $word) {
      if (strpos($value, mb_strtolower(trim($word))) !== FALSE) {
        $spam_error = TRUE;
      }
    }

    // Show web form error.
    if ($spam_error) {
      $formState->setErrorByName($field, $this->t('@spam_words_error', ['@spam_words_error' => $spam_words_error]));
    }
    else {
      $formState->setValue($field, $formState->getValue($field));
    }

  }

}
