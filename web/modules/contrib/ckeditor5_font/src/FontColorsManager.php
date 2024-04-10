<?php


declare(strict_types=1);

namespace Drupal\ckeditor5_font;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Font Colors plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class FontColorsManager extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface
{

    use CKEditor5PluginConfigurableTrait;


    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration(): array
    {
        return [
            'colors' => '[]'
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Form for choosing which heading tags are available.
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state): array
    {


        //Fieldset grouping all the saved colors
        $form['color-panel'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Colors'),
            '#group' => 'saved_colors',
            '#collapsible' => false,
            '#attributes' => ['id' => 'ckeditor-ui-colors-panel'],
        ];


        $form['color-panel']['color-template'] = [
            '#type' => 'inline_template',
            '#template' => '
              <template id="color-template">
                <div class="color">
                    <div class="delete-action"></div>
                    <span class="label"></span>
                </div>
              </template>
            ',
        ];

        //Add color form
        $form['color-add-form'] = [
            '#type' => 'inline_template',
            '#template' => '
              <div id="ckeditor-ui-new-color-panel">
                  <div class="form-item">
                      <label for="hex" class="form-item__label">' . $this->t("Color") . '</label>
                      <input id="hex" type="color" maxlength="7" required name="hex" placeholder="#18515E" class="form-text form-element form-element--type-text form-element--api-textfield"/>
                  </div>
                  <div class="form-item">
                      <label for="hex" class="form-item__label">' . $this->t("Name") . '</label>
                      <input id="color-label" type="text" maxlength="15" placeholder="Color label"  class="form-text form-element form-element--type-text form-element--api-textfield">
                  </div>
                  <div class="form-item">
                    <label class="form-item__label form-submit-label">&nbsp;</label>
                    <div class="editor-element-extra-margin button button--success js-form-submit form-submit">' . $this->t("Add") . '</div>
                  </div>
              </div>
            ',
        ];

        //System field to store JSON data
        $form['colors'] = [
            '#type' => 'hidden',
            '#attributes' => ['id' => 'colors-data-store', 'data-colors' => $this->configuration['colors']],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        // Match the config schema structure at ckeditor5.plugin.ckeditor5_colors.
        $colors = json_decode($form_state->getValue('colors') ?? '[]', true);
        $colors = $this->getValidColors($colors);

        $form_state->setValue('colors', json_encode($colors));
    }

    /**
     * {@inheritdoc}
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        $this->configuration['colors'] = $form_state->getValue('colors');
    }

    /**
     * {@inheritdoc}
     *
     */
    public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array
    {
        $data = $this->configuration['colors'];
        $colors = json_decode($data ?? '[]', true);

        return sizeof($colors) ? [
            'fontColor' => [
                'colors' => $colors
            ],
            'fontBackgroundColor' => [
                'colors' => $colors
            ]
        ] : [];
    }

    public function getValidColors($colors): array
    {
        return array_filter($colors, function ($clr) {
            return preg_match('/^#(?:[0-9a-f]{3}){1,2}$/i', $clr['color']);
        });
    }

    public function getElementsSubset(): array
    {
        return ['<p>'];
    }
}
