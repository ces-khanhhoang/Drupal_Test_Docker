<?php declare(strict_types=1);

namespace Drupal\freshteam\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\freshteam\Services\FreshTeamService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a Freshteam career synchronization
 *
 * @Block(
 *   id = "freshteam_career_synchronization",
 *   admin_label = @Translation("Freshteam career synchronization"),
 *   category = @Translation("Custom"),
 * )
 */
class FreshTeamJobsListBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * Freshteam Service.
   *
   * @var \Drupal\freshteam\Services\FreshTeamService
   */
  protected $freshteamService;

  /**
   * Constructs a new FreshTeamService instance.
   *
   * @param \Drupal\freshteam\Services\FreshTeamService $freshteam_service
   *   The service for interacting with job lists.
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The plugin ID for the block.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  public function __construct(FreshTeamService $freshteam_service, array $configuration, $plugin_id, $plugin_definition)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->freshteamService = $freshteam_service;
  }

  /**
   * Creates a new instance of the FreshteamBlock class.
   * 
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   An array of configuration options.
   * @param mixed $plugin_id
   *   The ID of the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin.
   *
   * @return static
   *   A new instance of the FreshteamBlock class.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $container->get('freshteam.service'),
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }


  /**
   * Implements hook_form().
   *
   * Form builder for the block configuration form.
   * 
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['content_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Title'),
      '#required' => FALSE,
      '#default_value' => $this->configuration['content_title'],
    ];

    $form['content_sub_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Sub Title'),
      '#required' => FALSE,
      '#default_value' => $this->configuration['content_sub_title'],
    ];

    $form['job_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Job Type'),
      '#required' => TRUE,
      '#options' => [
        'full_time' => $this->t('Full Time'),
        'intern' => $this->t('Intern'),
      ],
      '#default_value' => $this->configuration['job_type'],
    ];

    return $form;
  }

  /**
   * Implements hook_submit().  
   *
   * Form submission handler for the block configuration form.
   * 
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function blockSubmit($form, FormStateInterface $form_state)  
  {
    $values = $form_state->getValues();
    $this->configuration['job_type'] = $values['job_type'];
    $this->configuration['content_title'] = $values['content_title'];
    $this->configuration['content_sub_title'] = $values['content_sub_title'];
  }

  /**
   * Implements hook_build().
   *
   * Builds and returns the renderable array representing the block content.
   *
   * @return array
   *   A renderable array representing the block content.
   */
  public function build()
  {
    $jobType = $this->configuration['job_type'];
    $contentTitle = $this->configuration['content_title'];
    $contentSubTitle = $this->configuration['content_sub_title'];
    $jobsList = $this->freshteamService->getJobsList();

    return [
      '#theme' => 'jobs_list',
      '#data' => [
        "jobType" => $jobType,
        "jobsList" => json_decode($jobsList),
        "contentTitle" => $contentTitle,
        "contentSubTitle" => $contentSubTitle
      ],
      '#attached' => array(
        'library' => array('freshteam/freshteam'),
      ),
    ];
  }
}
