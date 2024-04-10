<?php

namespace Drupal\webform_spam_words\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for webform block spam words.
 */
class BlockwordsConfigurationSettings extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a settings controller.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['webform_spam_words.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wsw_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($this->moduleHandler->moduleExists('webform')) {

      // Get values from settings.
      $spam_words = $this->config('webform_spam_words.settings')->get('spam_words') ? implode(PHP_EOL, $this->config('webform_spam_words.settings')->get('spam_words')) : 'SEO';
      $spam_text_message = $this->config('webform_spam_words.settings')->get('spam_text_message') ? $this->config('webform_spam_words.settings')->get('spam_text_message') : 'Unable to submit form. Please contact the site administrator, if the problem persists.';
      $spam_field_name = $this->config('webform_spam_words.settings')->get('spam_field_name') ? $this->config('webform_spam_words.settings')->get('spam_field_name') : 'message';

      // Webform Block Spam Words Configuration.
      $form['config'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Webform Spam words Configuration'),
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
      ];

      $form['config']['spam_words'] = [
        '#type' => 'textarea',
        '#required' => TRUE,
        '#title' => $this->t('Webform Spam words'),
        '#default_value' => $spam_words,
        '#description' => $this->t('Each Webform spam words should be on a separate line.'),
      ];

      $form['config']['spam_text_message'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Webform Error Message'),
        '#default_value' => $spam_text_message,
        '#required' => TRUE,
        '#description' => $this->t("Please enter your error message, it shows on webform if it's failed. So, the spammers don't know your exact webform error message."),
      ];

      $form['config']['spam_field_name'] = [
        '#type' => 'textfield',
        '#required' => TRUE,
        '#title' => $this->t('Webform Field name(s)'),
        '#default_value' => $spam_field_name,
        '#description' => $this->t('Please enter your webform field(s). If you have more than one field, separate them with commas. For Example: message, email, name'),
      ];

      // Store the keys we want to save in configuration when form is submitted.
      $keys_to_save = array_keys($form['config']);
      foreach ($keys_to_save as $key => $key_to_save) {
        if (strpos($key_to_save, '#') !== FALSE) {
          unset($keys_to_save[$key]);
        }
      }
      $form_state->setStorage(['keys' => $keys_to_save]);

      $form['actions']['#type'] = 'container';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save configuration'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('webform_spam_words.settings');
    $storage = $form_state->getStorage();

    // Save configuration items from FormState values.
    foreach ($form_state->getValues() as $key => $value) {
      if (in_array($key, $storage['keys'])) {
        if ($key == 'spam_words') {
          $config->set($key, explode(PHP_EOL, trim($value)));
        }
        else {
          $config->set($key, $value);
        }
      }
    }

    // Save config settings.
    $config->save();

    // Message.
    $this->messenger->addMessage($this->t('The configuration has been saved.'));
  }

}
