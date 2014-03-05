wp-cli-quick-command
====================

Install and delete WordPress with one command.

Installation
============

 * https://github.com/wp-cli/wp-cli/wiki/Community-Packages#wiki-installing-a-package-without-composer
 * or https://github.com/wp-cli/wp-cli/wiki/Community-Packages#wiki-installing-a-package

Usage
=====

```bash
# create config.yml in your virtualhost directory then ...
wp quick install
wp quick delete *
```

Example output

```bash
$ wp quick install
Installing to /Users/mattes/Developer/php-unicorn/www/happy-tesla-58.vcap.me ...
Downloading WordPress 3.8.1 (en_US)...
Success: WordPress downloaded.
Success: Created 'wp-happy-tesla-58' database.
Success: Generated wp-config.php file.
Success: WordPress installed successfully.
```

See [config.example.yml](https://github.com/mattes/wp-cli-quick-command/blob/master/config.example.yml) 
for default values.