<?php

namespace Drupal\fontawesome_iconpicker_widget;

use Drupal\Component\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Extension\ThemeHandler;
use Symfony\Component\Yaml\Yaml;

class IconManagerService implements IconManagerServiceInterface {

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Drupal\Core\Extension\ThemeHandler definition.
   *
   * @var \Drupal\Core\Extension\ThemeHandler
   */
  protected $themeHandler;

  /**
   * Constructs a new IconManagerService object.
   *
   * @param Drupal\Core\Extension\ModuleHandler $module_handler
   * @param Drupal\Core\Extension\ThemeHandler $theme_handler
   */
  public function __construct(ModuleHandler $module_handler, ThemeHandler $theme_handler) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Get category metadata.
   *
   * @return array
   *   The available icon catagory metadata.
   */
  public function getCategoryMetadata() {
    
  }

  /**
   * Get icon categories.
   *
   * @return array $icon_categories
   */
  public function getIconCategories() {
    
  }

  /**
   * Get icons.
   *
   * @return array $icons
   */
  public function getIcons() {
    $icons = [];
    $iconData = fontawesome_extract_icons();
    $classes = [];

    foreach ($iconData as $icon => $data) {
      foreach ($iconData[$icon]['styles'] as $style) {
        switch ($style) {
          case 'brands':
            $iconPrefix = 'fab';
            break;
          case 'light':
            $iconPrefix = 'fal';
            break;
          case 'regular':
            $iconPrefix = 'far';
            break;
          case 'duotone':
            $iconPrefix = 'fad';
            break;
          default:
          case 'solid':
            $iconPrefix = 'fas';
            break;
        }
        $classes[$icon][] = $iconPrefix . ' fa-' . $icon;
      }
      $icons[] = [
        'name' => $iconData[$icon]['name'],
        'search_terms' => $iconData[$icon]['search_terms'],
        'classes' => $classes[$icon],
      ];
    }
    
    return $icons;
  }

  /**
   * Format icon list.
   *
   * @param array $icons
   *
   * @return array $formatted_icons_list
   */
  public function formatIconList(array $icons) {
    $icons_list = [];
    foreach ($icons as $name => $properties) {
      $icon_list[] = implode(', ', $properties['classes']);
    }
    $formatted_icon_list = explode(', ', implode(', ', $icon_list));
    return $formatted_icon_list;
  }

  /**
   * Format search terms.
   *
   * @param array $icons
   *
   * @return array $formatted_search_tersm
   */
  public function formatSearchTerms(array $icons) {
    $terms_list = [];
    foreach ($icons as $name => $properties) {
      foreach ($properties['classes'] as $item) {
        array_push($properties['search_terms'], $properties['name']);
        $terms_list[] = $properties['search_terms'];
      }
    }
    return $terms_list;    
  }

  /**
   * Get formatted icon list.
   *
   * @return array $icon_list
   */
  public function getFormattedIconList() {
    $icon_list = $this->formatIconList($this->getIcons());
    return $icon_list;
  }

  /**
   * Get formatted term list.
   *
   * @return array $terms_list
   */
  public function getFormattedTermList() {
    $terms_list = $this->formatSearchTerms($this->getIcons());
    return $terms_list;
  }

  /**
   * Get icon base name from class.
   *
   * @param string $class
   *
   * @return string $base
   */
  public function getIconBaseNameFromClass($class) {
    list($prefix, $base) = explode('fa-', $class);
    return $base;
  }

  /**
   * Get icon prefix from class.
   *
   * @param string $class
   *
   * @return string $prefix
   */
  public function getIconPrefixFromClass($class) {
    list($prefix, $base) = explode('fa-', $class);
    return trim($prefix);
  }

}
