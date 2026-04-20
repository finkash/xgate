# XGate - Mini Social Media Platform

XGate is a Laravel 12 application that implements a mini social platform with profiles, follow graph, feed, posts with mixed media, polymorphic reactions, and one-level nested comments.

## 1. What This App Does

### Main functions

- user registration, login, logout
- editable profile (bio, location, website, avatar, cover)
- follow and unfollow between users (self-follow blocked)
- post creation with text and optional media attachments
- post editing and deleting for post owners
- feed from followed users plus discover fallback when user follows nobody
- reactions on posts and comments with toggle behavior (`like`, `love`, `laugh`, `wow`, `sad`, `angry`)
- comments and one-level replies with owner edit/delete support

### UI and interaction style

- server-rendered Blade pages with Alpine.js for async interactions
- media gallery rendering for single, multiple, and video media
- profile pages showing followers/following and user posts

## 2. Tech Stack

- PHP 8.2+
- Laravel 12
- SQLite (default local setup)
- Tailwind CSS 4
- Alpine.js 3
- Vite
- PHPUnit

## 3. Important Project Files

- architecture document: `ARCHITECTURE.md`
- routes: `routes/web.php`
- domain code: `app/Domain/*`
- controllers: `app/Http/Controllers/*`
- views/components: `resources/views/*`
- migrations: `database/migrations/*`
- seeders: `database/seeders/*`
- tests: `tests/Feature/*`, `tests/Unit/*`

## 4. Database Is Included In The Repository

The development SQLite database file is tracked in git:

- `database/database.sqlite`

This means you can run the app quickly without creating a new database from scratch.

What this gives you:

- a pre-populated development snapshot
- realistic social data for testing UI and interactions
- faster onboarding for reviewers/contributors

Important notes:

- this is for local development/demo convenience
- do not use the tracked SQLite snapshot for production
- if you want a clean/fresh state, run migrations and seeders (see command reference below)

## 5. Prerequisites

Install these before running the project:

- PHP 8.2+ with `pdo_sqlite` and `sqlite3` enabled
- Composer
- Node.js 18+ and npm

## 6. Quick Start (Recommended)

### Step 1: install dependencies

```bash
composer install
npm install
```

### Step 2: prepare environment

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### Step 3: verify DB config (SQLite)

In `.env`, default is already SQLite:

```env
DB_CONNECTION=sqlite
```

No extra DB host/user/password values are needed for local SQLite mode.

### Step 4: ensure public storage symlink exists

```bash
php artisan storage:link
```

### Step 5: run the app

Option A (all-in-one local dev stack):

```bash
composer run dev
```

Option B (manual split terminals):

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

Then open:

- `http://127.0.0.1:8000`

## 6.1 Demo Login (No Sign-Up Needed)

Visitors can log in immediately using one of these seeded accounts:

- login email: `demo@example.com`  
	username (public handle): `demo_user`  
	password: `password`

- login email: `guest@example.com`  
	username (public handle): `guest_user`  
	password: `password`

These users are guaranteed by `SocialMediaDemoSeeder` and are also present in the tracked development SQLite database.

## 7. Optional: Reset Database To Fresh Demo State

If you want to rebuild from migrations and reseed:

```bash
php artisan migrate:fresh --seed
```

Seeder behavior:

- creates/normalizes 12 users
- creates profiles, follow graph, posts, comments/replies, reactions
- generates varied media patterns for feed coverage

## 8. Command Reference (What Each Command Does)

### Setup and local run

- `composer run setup`
	- installs PHP dependencies
	- prepares `.env`
	- generates app key
	- runs migrations
	- installs npm deps
	- builds frontend assets

- `composer run dev`
	- runs local stack concurrently:
		- Laravel server
		- queue listener
		- Laravel log tail (`pail`)
		- Vite dev server

### Database and cache

- `php artisan migrate`
	- applies pending migrations

- `php artisan migrate:fresh --seed`
	- drops all tables, recreates schema, seeds demo data

- `php artisan optimize:clear`
	- clears config, route, view, and other framework caches

### Storage/media

- `php artisan storage:link`
	- creates `public/storage` symlink to `storage/app/public`
	- required for uploaded media to be web-accessible

### Quality and tests

- `php artisan test`
	- runs full test suite

- `php artisan test --filter=FeedTest`
	- runs targeted test class or case

- `./vendor/bin/pint`
	- auto-formats PHP code style

### Frontend assets

- `npm run dev`
	- Vite development server with hot reload

- `npm run build`
	- production build of frontend assets

## 9. HTTP Endpoints and Functions

### Auth (guest)

- `GET /login` - login page
- `POST /login` - authenticate user
- `GET /register` - registration page
- `POST /register` - create account and log in

### Public

- `GET /profiles/{username}` - public profile page
- `GET /media/{path}` - serves media files from public disk

### Authenticated

- `GET /dashboard` - main feed page
- `POST /logout` - logout current user
- `POST /users/{user}/follow` - follow user
- `DELETE /users/{user}/follow` - unfollow user
- `POST /posts` - create post
- `PATCH /posts/{post}` - update post content
- `DELETE /posts/{post}` - delete post
- `GET /feed` - feed JSON endpoint (`per_page` supported)
- `GET /posts/{post}/comments` - list comments tree for post
- `POST /posts/{post}/comments` - create comment or reply
- `PATCH /posts/{post}/comments/{comment}` - edit own comment
- `DELETE /posts/{post}/comments/{comment}` - delete own comment
- `POST /posts/{post}/reactions` - toggle reaction on post
- `POST /posts/{post}/comments/{comment}/reactions` - toggle reaction on comment
- `GET /profile/edit` - edit own profile page
- `PUT /profile` - update own profile fields and images

## 10. Validation and Business Rules Summary

### Media validation

- images: jpg/jpeg/png/webp, max 5 MB each
- videos: mp4/webm, max 50 MB each

### Reaction toggle behavior

- one reaction per user per reactable entity
- same reaction again removes it
- different reaction updates the existing row

### Comments

- comments belong to posts
- replies are one level deep
- replies to replies are flattened under the original parent

### Ownership checks

- only post owner can edit/delete a post
- only comment owner can edit/delete a comment

## 11. Test Coverage

The repository includes both feature and unit tests for critical flows:

- reaction toggling (including polymorphic behavior)
- comment creation/update/delete and nesting
- feed visibility/filter behavior
- media validation behavior
- follow system and self-follow prevention
- feed/media service unit behavior

Run all tests:

```bash
php artisan test
```

## 12. Troubleshooting

### App does not load styles/scripts

- make sure `npm install` has been run
- run `npm run dev` (or `npm run build` for production build)

### Uploaded media not visible

- run `php artisan storage:link`
- check that files exist under `storage/app/public`

### SQLite errors

- ensure PHP has `pdo_sqlite` enabled
- ensure `database/database.sqlite` exists and is readable

### Session or cache odd behavior

- run `php artisan optimize:clear`

## 13. Contributor Notes

- keep controllers thin and domain logic inside actions/services
- use DTOs and enums where already established
- follow existing naming and structure in `app/Domain`
- use one logical change per commit with imperative commit messages
