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

### 4. FOLDER
The `folder` function checks if the directory path exists, if it doesn't, it creates the directory.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::folder('path...')
);
```

### 5. VALIDATE
The `validate` function checks if a file complies with the established validations (extensions). <br>
In this example it is established in the validation that it only allows the entry of `png, jpg` files.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::validate([
		'storage/code_letters_screen_137590_3840x2400.jpg',
		'storage/code_programming_text_140050_3840x2400.jpg'
	], ['png', 'jpg'])
);
```

### 6. UPLOAD
The `upload` function uploads files to a specified path.
```php
require_once 'vendor/autoload.php';

use LionFiles\FILES;

var_dump(
	FILES::upload(
		$_FILES['user_files']['tmp_name'],
		$_FILES['user_files']['name'],
		'storage/img/'
	)
);
```

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-Files/blob/main/LICENSE)