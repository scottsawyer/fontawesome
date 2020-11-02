<?php

namespace Drupal\fontawesome_iconpicker_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fontawesome\Plugin\Field\FieldWidget\FontAwesomeIconWidget;
use Drupal\fontawesome_iconpicker_widget\IconManagerServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fontawesome Iconpicker Widget.
 *
 * @FieldWidget(
 *   id = "fontawesome_iconpicker_widget",
 *   module = "fontawesome_iconpicker_widget",
 *   label = @Translation("Font Awesome Iconpicker Widget"),
 *   field_types = {
 *     "fontawesome_icon"
 *   }
 * )
 */
class FontAwesomeIconpickerWidget extends FontAwesomeIconWidget {

  /**
   * Drupal\fontawesome_iconpicker_widget\IconManagerInterface definition.
   *
   * @var \Drupal\fontawesome_iconpicker_widget\IconManagerInterface
   */
  protected $iconManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConfigFactory $config_factory, IconManagerServiceInterface $icon_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $config_factory);
    $this->iconManager = $icon_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('config.factory'),
      $container->get('fontawesome_iconpicker_widget.icon_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $icon_list = $this->iconManager->getFormattedIconList();
    $term_list = $this->iconManager->getFormattedTermList();

    $icon_style_default_value = $items[$delta]->get('style')->getValue();
    $icon_name_default_value = $items[$delta]->get('icon_name')->getValue();
    $icon_default = NULL;
    if ($icon_name_default_value && $icon_style_default_value) {
      $icon_default = $icon_style_default_value . ' fa-' . $icon_name_default_value;
    }

    $element['icon_name'] = [
      '#type' => 'textfield',
      '#title' => $cardinality == 1 ? $this->fieldDefinition->getLabel() : $this->t('Icon Name'),
      '#required' => $element['#required'],
      '#default_value' => $icon_default,
      '#attributes' => [
        'class' => ['fontawesome-iconpicker-icon'],
      ],
      '#attached' => [
	'library' => ['fontawesome_iconpicker_widget/fontawesome-iconpicker'],
        'drupalSettings' => [
          'fontawesomeIcons' => [
            'icons' => $icon_list,
            'terms' => $term_list,
          ],
        ],
      ],
      '#element_validate' => [
        [static::class, 'validateIconName'],
      ],
    ];

    $element['settings']['style'] = [
      '#type' => 'hidden',
      '#default_value' => $icon_style_default_value,
    ];
    // Get current settings.
    $iconSettings = unserialize($items[$delta]->get('settings')->getValue());

    $mask_icon = '';
    $mask_style = NULL;
    if (isset($iconSettings['masking']['mask']) && isset($iconSettings['masking']['style'])) {
      $mask_style = $iconSettings['masking']['style'];
      $mask_icon = $mask_style . ' fa-' . $iconSettings['masking']['mask'];
    }
    $element['settings']['masking']['mask'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon Name'),
      '#default_value' => $mask_icon,
      '#attributes' => [
        'class' => ['fontawesome-iconpicker-mask'],
      ],
      '#element_validate' => [
        [static::class, 'validateIconName'],
      ],
    ];

    $element['settings']['masking']['style'] = [
      '#type' => 'hidden',
      '#default_value' => $mask_style,
    ];

    return $element;
  }

  /**
   * Validate the Font Awesome icon name.
   */
  public static function validateIconName($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }

    $icon_base = \Drupal::service('fontawesome_iconpicker_widget.icon_manager')->getIconBaseNameFromClass($value);
    $iconData = fontawesome_extract_icon_metadata($icon_base);

    if (!isset($iconData['name'])) {
      $form_state->setError($element, t("Invalid icon"));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Load the icon data so we can determine the icon type.
    $metadata = fontawesome_extract_icons();

    // Loop over each item and set the data properly.
    foreach ($values as &$item) {
      // Reset $item['icon_name'] to the base name.
      $class = $item['icon_name'];
      $item['icon_name'] = $this->iconManager->getIconBaseNameFromClass($class);
      $item['style'] = $this->iconManager->getIconPrefixFromClass($class);
      unset($item['settings']['style']);

      if (!empty($item['settings']['masking']['mask'])) {
        $mask_class = $item['settings']['masking']['mask'];
        $item['settings']['masking']['mask'] = $this->iconManager->getIconBaseNameFromClass($mask_class);
        $item['settings']['masking']['style'] = $this->iconManager->getIconPrefixFromClass($mask_class);
      }

      // Massage rotate and flip values to make them format properly.
      if (is_numeric($item['settings']['power_transforms']['rotate']['value'])) {
        $item['settings']['power_transforms']['rotate']['type'] = 'rotate';
      }
      else {
        unset($item['settings']['power_transforms']['rotate']);
      }
      if (!empty($item['settings']['power_transforms']['flip-h']['value'])) {
        $item['settings']['power_transforms']['flip-h']['type'] = 'flip';
      }
      else {
        unset($item['settings']['power_transforms']['flip-h']);
      }
      if (!empty($item['settings']['power_transforms']['flip-v']['value'])) {
        $item['settings']['power_transforms']['flip-v']['type'] = 'flip';
      }
      else {
        unset($item['settings']['power_transforms']['flip-v']);
      }
 
      $item['settings'] = serialize(array_filter($item['settings']));
    }

    return $values;  
  }

}
