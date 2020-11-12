# Laravel Starter Kit

## Introduction

The Laravel Starter Kit is a compilation of packages that will help you speed up the development process when starting with a fresh Laravel project.

## What's Included

This starter kit includes the following composer packages:

- [Telescope](https://github.com/laravel/telescope)
- [Fortify](https://github.com/laravel/fortify)
- [Sentry](https://github.com/getsentry/sentry-laravel)
- [Migrations Organizer](https://github.com/JayBizzle/Laravel-Migrations-Organiser)
- [Dusk](https://github.com/laravel/dusk)

The following front-end packages will be installed:

- Bootstrap 4.5
- Vue 3

## Views

The following views will be generated:

```bash
views
    ├── auth
    │   ├── login.blade.php
    │   ├── passwords
    │   │   ├── confirm.blade.php
    │   │   ├── email.blade.php
    │   │   └── reset.blade.php
    │   ├── register.blade.php
    │   └── verify.blade.php
    ├── home.blade.php
    └── layouts
        └── app.blade.php
```

Authentication views will be registered with Fortify.

## Front-End Assets

The following front-end assets will be generated:

```bash
├── js
│   ├── ExampleComponent.vue
│   ├── app.js
│   └── bootstrap.js
└── sass
    ├── abstracts
    │   ├── _abstracts.scss
    │   ├── _colors.scss
    │   ├── _functions.scss
    │   ├── _mixins.scss
    │   └── _typography.scss
    ├── app.scss
    ├── base
    │   ├── _base.scss
    │   ├── _buttons.scss
    │   ├── _form-elements.scss
    │   ├── _headings.scss
    │   └── _links.scss
    ├── components
    │   └── _components.scss
    ├── layout
    │   ├── _dashboard.scss
    │   ├── _footer-main.scss
    │   ├── _header-main.scss
    │   └── _layout.scss
    ├── modules
    │   └── _modules.scss
    └── pages
        └── _pages.scss
```

## Vue

The starter kit uses Vue 3 and will provide you with an example component as well as your app already created and mounted.

## Requirements

- Laravel 8.2
- Node ^10 || ^12 || >=14
- npm ^6

## Getting Started

Add the starter kit to your composer file:

`composer require wisnet/laravel-starter-kit`

## Installation

Run `php artisan starter-kit:install` to install the packages.

Next, run migrations once all dependencies have been installed.

`php artisan migrate`

Run `npm install` to install packages.

Compile front-end assets by running `mix`

## Next Steps

If you're planning on using Sentry for error reporting make sure to get a DSN (visit [sentry.io](https://sentry.io/welcome/) to create a new account or access your existing one).

Next, run the following command to finish setting up Sentry:

`php artisan sentry:publish --dsn=paste-your-dsn-here`

Don't forget to uncomment the report method inside your application's exception handler.

## Documentation

The official documentation for each package can be found in the following links:

- [Telescope](https://laravel.com/docs/8.x/telescope)
- [Fortify](https://github.com/laravel/fortify)
- [Sentry](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Migrations Organizer](https://github.com/JayBizzle/Laravel-Migrations-Organiser)
- [Dusk](https://laravel.com/docs/8.x/dusk)
- [Vue](https://v3.vuejs.org/guide/installation.html)
- [Bootstrap](https://getbootstrap.com/docs/4.5/getting-started/introduction/)