
# Library Management System

![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue.svg)
![Composer](https://img.shields.io/badge/Composer-enabled-brightgreen.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)
![Status](https://img.shields.io/badge/status-active-success.svg)

A web-based Library Management System built with PHP. It allows users to search, buy, and manage books, register/login, and view transactions. The system is simple, extensible, and uses JSON for book data.

## Features
- Book search with auto-suggestions
- User registration and login
- Book purchase and transaction history
- Contact form for user queries
- Manager account creation
- Simple JSON-based book data
- Modular PHP pages for easy maintenance

## Project Structure
```
assets/
  css/         # Stylesheets
  images/      # Images
  js/          # JavaScript files
config.php     # Database config (sample)
data/
  books.json   # Book data
pages/
  books.php
  users.php
  transactions.php
  register.php
  process_contact.php
  process-login.php
  logout.php
  hash_password.php
  contact.php
  check-session.php
vendor/        # Composer dependencies
buy-book.php   # Book purchase logic
composer.json  # Composer config
create_manager.php # Manager creation
index.php      # Home page
suggest.php    # Book suggestion API
test.php       # Test script
```

## Requirements
- PHP 7.2 or higher
- Composer

## Setup
1. Clone the repository:
   ```sh
   git clone <repo-url>
   ```
2. Install dependencies:
   ```sh
   composer install
   ```
3. Add your book data to `data/books.json`.
4. Configure your database in `config.php` if needed.
5. Start a local PHP server:
   ```sh
   php -S localhost:8000
   ```
6. Open [http://localhost:8000](http://localhost:8000) in your browser.

## Usage
- Search for books using the search bar.
- Register or log in as a user.
- Buy books and view transaction history.
- Contact the library via the contact form.
- Create manager accounts for admin tasks.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
This project is licensed under the MIT License.
