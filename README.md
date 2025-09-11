# Dashboard
A Laravel 12 Dashboard

<!-- TOC -->
## Table of Contents
1. [Tech Stack](#tech-stack)
2. [General Information](#general-information)
3. [How To Contribute](#how-to-contribute)
4. [How To Setup](#how-to-setup)
5. [General CLI Commands](#general-cli-commands)
6. [Sponsor The Project](#sponsor-the-project)
<!-- /TOC -->

---

## Tech Stack

| Tech | Version |
|------|---------|
| PHP | 8.4.6 |
| Laravel Installer | 5.14.0 |
| Laravel | 12.28.1 |
| Composer | 2.8.8 |
| NPM | 11.5.2 |
| Node | v23.11.0 |
| VueJS | 3.5.18 |
| MySQL | 8.0.42 |

---

## General Information
A dashboard created in Laravel 12.
It will combine different aspects of what you could get from a dashboard, such as data management, blog posts, ticket tracking, and much more.

---

## How To Contribute
We welcome contributions! To contribute:

1. Fork the repository.
2. Create a new branch: `git checkout -b feature/your-feature-name`.
3. Make your changes and commit: `git commit -m 'Add your message here'`.
4. Run `php artisan insights` and make any relevant changes that it might suggest
5. Ensure there's relevant tests and that they work and pass.
6. If anything requires `vue.js` changes, run: `npm i && npm run build`.
7. Push to your fork: `git push origin feature/your-feature-name`.
8. Create a Pull Request.

Please follow the code style and commit message conventions.

---

## How To Setup

Follow these steps to set up the project locally:

1. Clone the repository
```bash
git clone https://github.com/MattYeend/Dashboard.git
cd AdminDashboard
```
2. Install PHP dependencies
```bash
composer install
```
3. Install Node dependencies
```bash
npm install && npm run build
```
4. Set up environment
```bash
cp .env.example .env
php artisan key:generate
```
5. Configure your database in `.env` and run migrations:
```bash
php artisan migrate
```
6. Seed all tables:
```bash
php artisan seed
```
7. Set up storage
```bash
php artisan storage:link
```
8. Run the development servers
```bash
php artisan serve
npm run dev
```

--- 

## General CLI Commands

| Command | Description |
| --- | --- |
| `php artisan make:model modelName -mcr` | Create a model, migration, and resource controller |
| `php artisan make:model modelName -a` or `php artisan make:model modelName --all` | Create a model, migration, factory, seeder, controller, resource, request(s) |
| `php artisan make:model modelName` | Create a model |
| `php artisan make:controller controllerName` | Create a controller |
| `php artisan make:controller controllerName --resource` | Create a resource controller |
| `php artisan make:migration migration_name` | Create a migration |
| `php artisan make:seeder SeederName` | Create a seeder |
| `php artisan make:factory FactoryName` | Create a factory |
| `php artisan make:request RequestName` | Creates a form request for validation |
| `php artisan make:event EventName` | Creates an event class |
| `php artisan make:listener ListenerName` | Creates a listener class |
| `php artisan make:job JobName` | Creates a queued job |

--- 

## Sponsor The Project
If you find this project useful, consider sponsoring it to support future development and maintenance.
<a href="https://www.buymeacoffee.com/mattyeend">☕ Buy Me a Coffee</a>
<a href="https://github.com/sponsors/MattYeend">💸 Personal GitHub Sponsor</a> or <a href="https://github.com/sponsors/MatthewYeend">Company Github Sponsor</a>
