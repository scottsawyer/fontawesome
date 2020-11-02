<?php

namespace Drupal\fontawesome_iconpicker_widget;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Extension\ThemeHandler;

/**
 * Provides an interface for icon manager.
 */
interface IconManagerServiceInterface {

  /**
   * Get formatted icon list.
   *
   * @return array $icon_list
   *   An array of icons.
   */
  public function getFormattedIconList();

  /**
   * Get formatted term list.
   *
   * @return array $term_list
   *   An array of search terms.
   */
  public function getFormattedTermList();

  /**
   * Get icon base name from class.
   *
   * @param string $class
   *   The icon class name.
   *
   * @return string $base
   *   The icon base name.
   */
  public function getIconBaseNameFromClass($class);

  /**
   * Get icon prefix from class.
   *
   * @param string $class
   *   The icon class name.
   *
   * @return string $prefix
   *   The icon style prefix.
   */
  public function getIconPrefixFromClass($class);

}
