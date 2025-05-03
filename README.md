# Geezap Job Scraper

A robust web scraping tool built to collect job listings from multiple sources for [Geezap](https://github.com/theihasan/geezap), a comprehensive job aggregation platform.

## Overview

This project serves as the data collection backbone for Geezap, automating the process of gathering job listings from various sources and consolidating them into a centralized database.

## Features

- Multi-source job data collection
- Automated scraping using Browsershot
- Efficient HTML parsing with Symfony DOM Crawler
- Seamless integration with Geezap's database
- Robust error handling and retry mechanisms
- Rate limiting to respect source websites

## Tech Stack

- **Framework:** Laravel
- **Scraping:** Spatie Browsershot
- **Parsing:** Symfony DOM Crawler
- **Database:** MySQL/PostgreSQL (compatible with Geezap's schema)

## Requirements

- PHP >= 8.1
- Node.js >= 16
- Composer
- NPM
- Chrome/Chromium (for Browsershot)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/your-username/geezcrap.git
```
2. Install PHP dependencies:
```bash
composer install
```
3. Install Node.js dependencies:
```bash
npm install
```
4. Configure your database settings in `.env` file.
```bash
cp .env.example .env
```
5. Update your database credentials and other settings.
6. Run the database migrations:
```bash
php artisan migrate
```
7. Start the development server:
```bash
php artisan serve
```

## License
This project is open-source and available under the [MIT License](LICENSE).

## Acknowledgments
- [Geezap](https://github.com/theihasan/geezap) - The main job aggregation platform
- [Spatie Browsershot](https://github.com/spatie/browsershot) - For HTML rendering
- [Symfony DOM Crawler](https://symfony.com/doc/current/components/dom_crawler.html) - For HTML parsing
