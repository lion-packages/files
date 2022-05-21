# Lion-Files
Library created with the function of working internal system files.

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-files/v)](https://packagist.org/packages/lion-framework/lion-files) [![Total Downloads](http://poser.pugx.org/lion-framework/lion-files/downloads)](https://packagist.org/packages/lion-framework/lion-files) [![Latest Unstable Version](http://poser.pugx.org/lion-framework/lion-files/v/unstable)](https://packagist.org/packages/lion-framework/lion-files) [![License](http://poser.pugx.org/lion-framework/lion-files/license)](https://packagist.org/packages/lion-framework/lion-files) [![PHP Version Require](http://poser.pugx.org/lion-framework/lion-files/require/php)](https://packagist.org/packages/lion-framework/lion-files)

## Install
```
composer require lion-framework/lion-files
```

## Usage
### 1. GET EXTENSION
The `getExtension` function is available to get the extension of a specific file.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::getExtension('path/myfile...');
);
```

### 2. GET NAME
The `getName` function is available to get the name of a specific file.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::getName('path/myfile...');
);
```

### 3. GET BASE NAME
The `getBasename` function is available to get the name and extension of a specific file.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::getBasename('path/myfile...');
);
```

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-Files/blob/main/LICENSE)