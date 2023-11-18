# INNA

INNA: A PHP minimal framework which allows you to control every bit of it.

![Screenshot][def]

[def]: screenshot.png

# HOW TO USE

- Install composer globally. Do the following on a Linux. Visit [here](https://getcomposer.org/doc/00-intro.md) for windows.

```bash
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
$ mv composer.phar /usr/local/bin/composer
```

- Create a new project using composer.

```bash
$ composer create-project ekwachie/inna-framework [project_name] -s dev
```

# Todo
- Create an easy to use dashboaord for admin manipulations like in Django Framework.
