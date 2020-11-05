# Laravel Starter Kit

## Introduction

The Laravel Starter Kit is a compilation of packages that will help you speed up the development process when starting with a fresh Laravel project.

This package includes:

- [Telescope](https://github.com/laravel/telescope)
- [Fortify](https://github.com/laravel/fortify)
- [Sentry](https://github.com/getsentry/sentry-laravel)
- [Migrations Organizer](https://github.com/JayBizzle/Laravel-Migrations-Organiser)


## Requirements

- Laravel 8.2

## Getting Started

Add the starter kit to your composer file:

`composer require wisnet/laravel-starter-kit`

## Installation

Run `php artisan starter-kit:install` to install the packages.

Next, run migrations once all dependencies have been installed.

`php artisan migrate`

## Next Steps

If you're planning on using Sentry for error reporting make sure to get a DSN (visit [sentry.io](https://sentry.io/welcome/) to create a new account or access your existing one).

Next, run the following command to finish setting up Sentry:

`php artisan sentry:publish --dsn=paste-your-dsn-here`

## Documentation

The official documentation for each package can be found in the following links:

- [Telescope](https://laravel.com/docs/8.x/telescope)
- [Fortify](https://github.com/laravel/fortify)
- [Sentry](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Migrations Organizer](https://github.com/JayBizzle/Laravel-Migrations-Organiser)