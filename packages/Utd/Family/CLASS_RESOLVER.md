# ClassResolver for Family Package

## Overview

This package uses **ClassResolver** to decoule itself from the main application classes.

## Example Usage

```php
use Utd\Family\Support\ClassResolver;

$userModel = ClassResolver::model('User');
$user = $userModel::find(1);
```
