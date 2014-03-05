<?php
/**
 * Quick WordPress helpers.
 */
class Quick_Command extends WP_CLI_Command {

  /**
   * Quickly install WordPress using defaults from config.yml
   *
   * ## OPTIONS
   *
   * [--path=<path>]
   * : Specify the path in which to install WordPress. Defaults to current directory.
   *
   * [--version=<version>]
   * : Select which version you want to download. Defaults to latest.
   *
   * [--locale=<locale>]
   * : Select which language you want to download. Defaults to 'en'.
   *
   * [--open-url]
   * : Open URL after installation in browser.
   *
   * ## EXAMPLES
   *
   *     wp quick install
   *
   * @when before_wp_load
   */
  public function install($args, $assoc_args) {
    // rescue directory
    $rescue_directory = getcwd();

    // get extra config
    $extra_config = \WP_CLI::get_runner()->extra_config;

    $extra_config['quick install']['domain']     = isset($extra_config['quick install']['domain']) ? $extra_config['quick install']['domain'] : null;
    $extra_config['quick install']['http_port']  = isset($extra_config['quick install']['http_port']) ? $extra_config['quick install']['http_port'] : null;

    $core_download_args = array_merge(
      array('path' => getcwd()),
      isset($extra_config['core download']) ? $extra_config['core download']: array(), 
      $this->_array_key_filter($assoc_args, 
        array('path', 'locale', 'version')));

    // create random name
    $retries = 3;
    while($retries > 0) {
      $name = $this->_generate_random_name();
      if(!file_exists($core_download_args['path'] . '/'. $name . (!empty($extra_config['quick install']['domain']) ? $extra_config['quick install']['domain'] : ''))) {
        break;
      }
      $retries--;
    }

    // append name to path
    $core_download_args['path'] .= '/' . $name . (!empty($extra_config['quick install']['domain']) ? $extra_config['quick install']['domain'] : '');

    mkdir($core_download_args['path']); // it does not exist, yet
    chdir($core_download_args['path']);
    WP_CLI::log(sprintf('Installing to %s ...', $core_download_args['path']));
    
    // 1) download ...
    $this->_call_internal_command('core download', array(), $core_download_args);


    // 2) config ...
    $core_config_args = array_merge(
      isset($extra_config['core config']) ? $extra_config['core config'] : array(), 
      array()); 
    

    if(!empty($core_config_args['dbname']) && !empty($core_config_args['dbprefix'])) {
      WP_CLI::error('dbname and dbprefix are set in your config file!');
      return;
    }

    if(empty($core_config_args['dbname'])) {
      // create db first
      $core_config_args['dbname'] = 'wp-' . $name;
      $mysqli = new mysqli($core_config_args['dbhost'], $core_config_args['dbuser'], $core_config_args['dbpass']);
      if(!$mysqli) {
        WP_CLI::error(sprintf("Unable to connect to database '%s' with user '%s'.", $core_config_args['dbhost'], $core_config_args['dbuser']));
        return;
      }
      // @todo check if schema exists and throw error
      if(!$mysqli->query("CREATE SCHEMA `" . $core_config_args['dbname']. "` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")) {
        WP_CLI::error(sprintf("Unable to create new schema '%s'.", $core_config_args['dbname']));
        return;
      }
      $mysqli->close();
      WP_CLI::success(sprintf("Created '%s' database.", $core_config_args['dbname']));
    }
    elseif(empty($core_config_args['dbprefix'])) {
      // auto generate dbprefix
      $core_config_args['dbprefix'] = 'wp_' . $name;
    }


    $this->_call_internal_command('core config', array(), $core_config_args);


    // 3) install ..
    $core_install_args = array_merge(
      array(
        'admin_user'      => 'admin',
        'admin_password'  => 'admin123',
        'admin_email'     => 'admin@example.com'
      ),
      isset($extra_config['core install']) ? $extra_config['core install'] : array());
  
    $core_install_args['title'] = $name;
    $core_install_args['url'] = 'http://' . $name . (!empty($extra_config['quick install']['domain']) ? $extra_config['quick install']['domain'] : '') . (!empty($extra_config['quick install']['http_port']) ? ':' . $extra_config['quick install']['http_port'] : '');
    $this->_call_internal_command('core install', array(), $core_install_args);


    // rescue directory
    chdir($rescue_directory);

    if(isset($assoc_args['open-url'])) {
      WP_CLI::launch('open ' . $core_install_args['url']);
    }
  }



  private function _call_internal_command($name, $args = array(), $assoc_args = array()) {
    // work-around for bug, see https://github.com/wp-cli/wp-cli/pull/647
    // this won't work:
    // WP_CLI::run_command( array('core', 'download'), $this->_array_key_filter($assoc_args, array('path', 'version', 'locale')) );

    $string = 'wp ' . $name;

    foreach ($args as $value) {
      $string .= ' --' . $value;
    }

    foreach ($assoc_args as $key => $value) {
      $string .= ' ' . sprintf('--%s="%s"', $key, $value);
    }

    WP_CLI::launch($string);
  }


  private function _array_key_filter($array, $keys) {
    return array_intersect_key($array, array_flip($keys));
  }


  private function _generate_random_name($join="-") {
    // Docker 0.7.x generates names from notable scientists and hackers.
    // copied from https://github.com/dotcloud/docker/blob/master/pkg/namesgenerator/names-generator.go
    $left = ["happy", "jolly", "dreamy", "sad", "angry", "pensive", "focused", "sleepy", "grave", "distracted", "determined", "stoic", "stupefied", "sharp", "agitated", "cocky", "tender", "goofy", "furious", "desperate", "hopeful", "compassionate", "silly", "lonely", "condescending", "naughty", "kickass", "drunk", "boring", "nostalgic", "ecstatic", "insane", "cranky", "mad", "jovial", "sick", "hungry", "thirsty", "elegant", "backstabbing", "clever", "trusting", "loving", "suspicious", "berserk", "high", "romantic", "prickly", "evil"];
    $right = ["lovelace", "franklin", "tesla", "einstein", "bohr", "davinci", "pasteur", "nobel", "curie", "darwin", "turing", "ritchie", "torvalds", "pike", "thompson", "wozniak", "galileo", "euclid", "newton", "fermat", "archimedes", "poincare", "heisenberg", "feynman", "hawking", "fermi", "pare", "mccarthy", "engelbart", "babbage", "albattani", "ptolemy", "bell", "wright", "lumiere", "morse", "mclean", "brown", "bardeen", "brattain", "shockley"];

    return $left[array_rand($left)] . $join . $right[array_rand($right)] . $join . rand(1, 99);
  }

}

WP_CLI::add_command( 'quick', 'Quick_Command' );

