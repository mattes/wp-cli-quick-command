wp-cli-quick-command
====================

Install WordPress with one command.

# DO NOT USE, YET!!!!


Installation
============

See https://github.com/wp-cli/wp-cli/wiki/Community-Packages#installing-community-packages-manually
for more detailed installation instructions.

```bash
# find wp-cli's composer.json file and change to that directory
# examples:
# cd ~/.wp-cli (default installation directory)
# cd /usr/local/opt/wp-cli (when installed with homebrew)

# add package index if not done yet
composer config repositories.wp-cli composer http://wp-cli.org/package-index/

# install wp-cli-git-command
composer require mattes/wp-cli-quick-install-command=dev-master

# update with
composer update
```

Usage
=====

```bash
cd /virtual-hosts
wp quick-install xyz @todo
```


--------------

## Developer Note
I locally develop this plugin by setting a symlink. YOU don't have to do this.

```
ln -s $(pwd)/wp-cli-quick-install-command.php /usr/local/opt/wp-cli/php/commands/wp-cli-quick-install-command.php
```