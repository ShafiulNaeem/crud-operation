# CRUD Operation

## Table of Contents
* [Description](#description)
* [Installation](#installation)


## Description
Welcome to the documentation for ```CRUD Operation``` , a powerful CRUD (Create, Read, Update, Delete) operations generator for Laravel. This package is designed to streamline the process of creating CRUD operations for your models, saving you time and effort in the development process.

## Installation
👉 To install this package, you'll need to have Laravel version 10 or higher and PHP version 8.0.0 or higher installed on your machine. You can download the latest version of PHP from the official PHP resource: https://www.php.net/downloads.php. Ensure your environment meets these requirements before proceeding with the installation.

- Install package:
  ```
     composer require shafiulnaeem/crud-operation 
  ```
- Generate CRUD operations for your model effortlessly with the following Artisan command:
  
  ```
    php artisan add:crud {your-model} {column-type-validationRule}
  ```
    - ```{your-model}```: Replace with your desired model name.
    - ```{column-type-validationRule}```: Provide a list of columns along with their data types and validation rules.
    - **Example:**
      ```
        php artisan add:crud User name-string-required,email-string-required,password-string-required,age-integer-nullable
      ```
      - This example creates CRUD operations for a ```User``` model with columns ```name```, ```email```, ```password```, ```and``` age. Column types and validation rules are specified for each column.
      - **Generated Files:** After running the command, package will generate the following files in your Laravel project:
        1. Model file ``app/Models/{YourModel}.php``
        2. Migration file ``database/migrations/{timestamp}_create_{your_model_pluralized}_table.php``
        3. Controller file ``app/Http/Controllers/{YourModel}Controller.php``
        4. Route entry in ``web.php`` file 
        5. Request file ``app/Http/Requests/{YourModel}Request.php``
        6. Blade view files ``resources/views/{your_model_pluralized``

