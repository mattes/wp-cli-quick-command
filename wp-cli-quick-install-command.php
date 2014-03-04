<?php
/**
 * Quickly install WordPress
 */
class Quick_Command extends WP_CLI_Command {

  /**
   * Get information about WP-CLI itself.
   *
   * @when before_wp_load
   */
  function install($args, $assoc_args) {
    $skipped_plugins = \WP_CLI::get_runner()->extra_config["core install"];

    WP_CLI::warning(print_r($skipped_plugins, true));
  }

}

WP_CLI::add_command( 'quick', 'Quick_Command' );

