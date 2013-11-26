<?php

class Quick_Install_Command extends WP_CLI_Command {

  /**
   *  @when before_wp_load
   */
  function test($args, $assoc_args) {
    WP_CLI::warning("Hello!");
  }

}

WP_CLI::add_command( 'quick-install', 'Quick_Install_Command' );

