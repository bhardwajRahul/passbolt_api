# Developers FAQ
## Prerequisite
Make sure you have the developement dependencies install.
```
composer install
```

## How do I run the unit tests
- Configure your test database in app.php datasources section.
- Run phpunit:
```
composer test
```

## How do I check the code standards
- To display the error and warning
```
composer cs-check
```
- To autofix what is fixable
```
composer cs-fix
```

## How to regenerate the fixtures
```
sudo su -s /bin/bash -c "./bin/cake PassboltTestData.fixturize default" www-data
```

## How do I contribute to the the js application

Clone the appjs repository in a separate folder
```
git clone https://github.com/passbolt/passbolt_styleguide.git
```

In your passbolt_api folder install the javascript dependencies
```
npm install
```

Link the source of passbolt_styleguide project to your passbolt_api project
```
cd node_modules
rm -fr passbolt-styleguide
npm link ../../passbolt-styleguide
cd ../
```

## How do I contribute to the translation

For contributing to the translations of this repository, you will need to create an account and propose changes at https://passbolt.crowdin.com/.

