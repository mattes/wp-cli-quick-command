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
   * ## EXAMPLES
   *
   *     wp quick install
   *
   * @when before_wp_load
   */
  public function install($args, $assoc_args) {
    // rescue directory
    $rescue_directory = getcwd();

    // parse some args
    if(empty($assoc_args['path'])) $assoc_args['path'] = getcwd();

    // create random name
    $retries = 3;
    while($retries > 0) {
      $name = $this->_generate_random_name();
      if(!file_exists($assoc_args['path'] . '/'. $name)) {
        break;
      }
      $retries--;
    }

    // append name to path
    $assoc_args['path'] .= '/' . $name;

    WP_CLI::log(sprintf("Installing to %s", $assoc_args['path']));
    mkdir($assoc_args['path']); // it does not exist, yet
    chdir($assoc_args['path']);
    
    // 1) download ...
    $this->_call_internal_command('core download', array(), $this->_array_key_filter($assoc_args, array('path', 'version', 'locale')));

    // 2) config ...
    $this->_call_internal_command('core config');

    // 3) install ..
    $this->_call_internal_command('core install');

    

    // $skipped_plugins = \WP_CLI::get_runner()->extra_config["core install"];

    // WP_CLI::warning(print_r($skipped_plugins, true));

  


    // rescue directory
    chdir($rescue_directory);

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

