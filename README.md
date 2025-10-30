# INNA

INNA: A PHP minimal framework which allows you to control every bit of it. Special thanks to our Payperlez Team working on this.

![Screenshot][def]

[def]: screenshot.png

# HOW TO USE

- Install composer globally. Do the following on a Linux. Visit [here](https://getcomposer.org/doc/00-intro.md) for windows.

```bash
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
$ mv composer.phar /usr/local/bin/composer
```

- Create a new project using composer.

```bash
$ composer create-project ekwachie/inna-framework [project_name] -s stable
```

- Generate Keys
```bash
 $ openssl rand -hex 32
```       

- Create migrations

```bash
$ ./migrations add table_name column:type column:type ...
```

- Apply migrations

```bash
$ ./migrations update
```

- Built With [ List of applications using INNA PHP FRAMEWORK ]
```bash
1. myblogpay.com
```

# Todo
- Work on database migrations [ Alter tables etc ]
