# Laravel Sluggable Trait

Create unique slugs for your Eloquent models in Laravel. Support Cache

## Usage

```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use DuyDucNguyen\Database\Traits\SluggableTrait;

class Item extends Model {

	use SluggableTrait;

}
```

First, you need to indicate sluggable column and slug column of your model via class constants:

```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use DuyDucNguyen\Database\Traits\SluggableTrait;

class Item extends Model {

	use Sluggable;

	const SLUGGABLE_COLUMN = 'title';
	const SLUG = 'slug';

}
```

If you need to improve your query for searching slug, you can enable Laravel Cache for caching slug via 

```php

const ENABLE_SLUG_CACHE = true;

```



## License

Licensed under the [MIT Licence](LICENSE.md).