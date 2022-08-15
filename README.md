# Lion-Files
Library created with the function of working internal system files.

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-files/v)](https://packagist.org/packages/lion-framework/lion-files) [![Total Downloads](http://poser.pugx.org/lion-framework/lion-files/downloads)](https://packagist.org/packages/lion-framework/lion-files) [![License](http://poser.pugx.org/lion-framework/lion-files/license)](https://packagist.org/packages/lion-framework/lion-files) [![PHP Version Require](http://poser.pugx.org/lion-framework/lion-files/require/php)](https://packagist.org/packages/lion-framework/lion-files)

## Install
```
composer require lion-framework/lion-files
```

## Usage
### 1. GET EXTENSION
The `getExtension` function is available to get the extension of a specific file
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::getExtension('path/myfile...');
);
```

### 2. GET NAME
The `getName` function is available to get the name of a specific file
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::getName('path/myfile...');
);
```

### 3. GET BASE NAME
The `getBasename` function is available to get the name and extension of a specific file
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::getBasename('path/myfile...');
);
```

### 4. FOLDER
The `folder` function checks if the directory path exists, if it doesn't, it creates the directory
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::folder('path...')
);
```

### 5. VALIDATE
The `validate` function checks if a file complies with the established validations (extensions). <br>
In this example it is established in the validation that it only allows the entry of `png, jpg` files
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::validate([
		'storage/code_letters_screen_137590_3840x2400.jpg',
		'storage/code_programming_text_140050_3840x2400.jpg'
	], ['png', 'jpg'])
);
```

### 6. UPLOAD
The `upload` function allows you to upload files to a path
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::upload(
		$_FILES['user_files']['tmp_name'],
		$_FILES['user_files']['name'],
		'storage/img/'
	)
);
```

### 7. RENAME
The `rename` function renames files with random characters, it allows to add a callsign to each file when it is renamed, the `rename` function uses `md5(hash('sha256', uniqid()))` to randomly rename files
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::rename($_FILES['user_files']['name'])
);
// example output => string(40) "141539cf52f48ecdc5008a19d62ede3b.jpg"

// or

var_dump(
	Files::rename(
		$_FILES['user_files']['name'],
		'IMG'
	)
);
// example output => string(40) "IMG-141539cf52f48ecdc5008a19d62ede3b.jpg"
```

### 8. EXIST
The `exist` function allows you to check if a folder or file exists
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::exist('path...')
);
```

### 9. REMOVE
The `remove` function allows you to delete files
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::remove('path...')
);
```

### 10. VIEW
The `view` function gets a list of the files found within a path
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::view('path...')
);
```

### 11. SIZE
The `size` function checks if a file meets the required size in KB
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::size('path...', 500)
);
```

### 12. IMAGE SIZE
The `imageSize` function allows you to check if an image meets the requested dimensions
```php
require_once 'vendor/autoload.php';

use LionFiles\Files;

var_dump(
	Files::imageSize('folder', 'file', '1920x1080')
);
```

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-Files/blob/main/LICENSE)