# XGate Architecture

This document describes the current architecture of the Mini Social Media Platform as implemented in this repository.

## 1. System Overview

XGate is a Laravel 12 social app with three business domains:

- IdentityAndAccess: authentication, profile management, and follow graph
- Content: posts, media uploads, and feed assembly
- Engagement: reactions and comments with one-level reply support

The app uses server-rendered Blade pages with Alpine.js-enhanced interactions for actions such as reaction toggling and comment operations.

## 2. Tech Stack and Runtime Model

- Backend: Laravel 12, PHP 8.2+
- Data layer: Eloquent ORM + migrations + seeders
- Database: SQLite (tracked development snapshot included in `database/database.sqlite`)
- Frontend: Blade templates + Tailwind CSS 4 + Alpine.js
- Build tooling: Vite
- Tests: PHPUnit feature and unit tests

## 3. Bounded Contexts and Folder Structure

### IdentityAndAccess

Path: `app/Domain/IdentityAndAccess`

- Models:
  - `Profile`
  - `Follow`
- Actions:
  - `RegisterUserAction`
  - `ToggleFollowAction`
- DTOs:
  - `RegisterUserDTO`
- Seed enums:
  - `SeedFirstName`
  - `SeedLastName`

Primary responsibilities:

- registration workflow and initial profile creation
- follow/unfollow business rules (including self-follow prevention)
- public profile graph data (followers/following)

### Content

Path: `app/Domain/Content`

- Models:
  - `Post`
  - `PostMedia`
- Actions:
  - `CreatePostAction`
  - `UpdatePostAction`
  - `DeletePostAction`
- DTOs:
  - `CreatePostDTO`
- Services:
  - `FeedService`
  - `MediaUploadService`
- Enums:
  - `MediaType`

Primary responsibilities:

- post lifecycle management
- media validation and storage
- feed query composition and engagement hydration

### Engagement

Path: `app/Domain/Engagement`

- Models:
  - `Reaction`
  - `Comment`
- Actions:
  - `ToggleReactionAction`
  - `CreateCommentAction`
  - `UpdateCommentAction`
  - `DeleteCommentAction`
- DTOs:
  - `CreateCommentDTO`
- Enums:
  - `ReactionType`

Primary responsibilities:

- polymorphic reaction toggle behavior
- comment/reply creation and ownership-safe updates/deletes

## 4. Application Layers

### HTTP Controllers (Transport Layer)

Path: `app/Http/Controllers`

Controllers validate requests, authorize ownership checks where needed, call domain actions/services, and return JSON or redirect responses.

Key controllers:

- `AuthController`
- `PostController`
- `CommentController`
- `ReactionController`
- `FollowController`
- `FeedController`
- `ProfileController`

### Domain Actions (Use-Case Layer)

Actions encapsulate one business use case each. Multi-step writes are wrapped in database transactions where appropriate.

Examples:

- `CreatePostAction` creates a post and related media rows in one transaction
- `ToggleReactionAction` enforces one reaction per user per reactable entity
- `DeletePostAction` performs explicit cleanup for comment reactions, post reactions, comments, media, then force-deletes the post

### Domain Services (Query/Orchestration Layer)

- `FeedService` builds the followed-user feed with discover fallback and hydrates reaction metadata
- `MediaUploadService` validates mime/size and stores uploaded files to the `public` disk

### Models (Relationship Layer)

Eloquent models define table-level behavior and relationships:

- `Post` -> `author`, `media`, `comments`, `topLevelComments`, `reactions`
- `Comment` -> `post`, `user`, `parent`, `replies`, `reactions`
- `Reaction` -> `user`, `reactable` (`morphTo`)
- `User` -> `profile`, `followers`, `following`

## 5. Core Data Model

Implemented social tables (current schema):

- `profiles` (ULID PK, one-to-one with users)
- `follows` (ULID PK, unique `follower_id + following_id`)
- `posts` (ULID PK, soft deletes)
- `post_media` (ULID PK, `type`, `display_order`, optional `alt_text`)
- `comments` (ULID PK, optional `parent_comment_id`, soft deletes)
- `reactions` (ULID PK, polymorphic `reactable_type + reactable_id`, unique user per reactable)

Supporting framework tables:

- `users` (integer PK)
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `migrations`, `password_reset_tokens`

Important indexing and constraints:

- unique one-reaction rule: `reactions(user_id, reactable_type, reactable_id)`
- unique follow edge: `follows(follower_id, following_id)`
- feed performance indexes on `posts.created_at`, relationship FKs, and comment hierarchy fields

## 6. Reaction Polymorphism

Reactions are implemented as one polymorphic table shared by posts and comments.

- `Post` and `Comment` each expose `morphMany(Reaction::class, 'reactable')`
- `Reaction` exposes `morphTo()`

Toggle algorithm in `ToggleReactionAction`:

1. Find existing reaction for `user + reactable`.
2. If none exists, create with requested type.
3. If existing type equals requested type, delete it (toggle off).
4. If existing type differs, update it.
5. Return grouped counts for all reaction types.

## 7. Feed Construction and Hydration

`FeedService::getFeed(User $user, int $perPage = 15)` behavior:

1. Read followed user IDs from `follows`.
2. Build base post query with eager loading:
   - `author.profile`
   - `media`
   - `topLevelComments.user.profile`
   - `topLevelComments.replies.user.profile`
3. Apply mode:
   - following mode: followed users + own posts, newest first
   - discover mode: order by total reaction count, then recency
4. Paginate.
5. Hydrate engagement attributes for each post/comment/reply:
   - `reaction_summary`
   - `current_user_reaction` (when authenticated user is available)

## 8. Comment Model and Nesting Rule

Comments support one-level nesting:

- top-level comments have `parent_comment_id = null`
- replies set `parent_comment_id` to top-level comment ID
- replies-to-replies are flattened under the original top-level parent

Creation rule is implemented in `CreateCommentAction` by resolving nested replies to the root parent.

## 9. Media Handling

`MediaUploadService` accepts uploaded files and applies media-type-specific validation:

- images: jpeg/png/webp up to 5 MB
- videos: mp4/webm up to 50 MB

Files are stored on the `public` disk under `posts/{user_id}` and persisted to `post_media` with `display_order` for deterministic gallery rendering.

Profile uploads:

- avatar and cover are handled in `ProfileController::update()` and stored under `avatars/` and `covers/`.

## 10. UI Composition

Page templates:

- auth:
  - `resources/views/auth/login.blade.php`
  - `resources/views/auth/register.blade.php`
- feed:
  - `resources/views/livewire/feed/index.blade.php`
- profile:
  - `resources/views/livewire/profile/show.blade.php`
  - `resources/views/livewire/profile/edit.blade.php`

Reusable components:

- `resources/views/livewire/components/post-card.blade.php`
- `resources/views/livewire/components/reaction-bar.blade.php`
- `resources/views/livewire/components/comment-thread.blade.php`
- `resources/views/livewire/components/media-gallery.blade.php`

Layout:

- `resources/views/layouts/neon.blade.php`

## 11. Route Topology

Routes are defined in `routes/web.php` and split into guest/public/auth-protected sections.

- guest:
  - login/register pages and submissions
- public:
  - profile show by username
  - media proxy endpoint (`/media/{path}`) from public disk
- auth:
  - dashboard/feed
  - profile edit/update
  - post create/update/delete
  - comment CRUD
  - reaction toggles on posts/comments
  - follow/unfollow
  - logout

## 12. Data Seeding and Sample Dataset

`DatabaseSeeder` calls `SocialMediaDemoSeeder`, which:

- ensures at least 12 users
- generates varied profiles
- creates follow graph (3-5 follows per user)
- creates 30+ posts with mixed media patterns
- creates top-level comments and one-level replies
- creates reactions on posts and comments across all reaction types

This provides a realistic, non-empty environment for manual QA and demos.

## 13. Testing Strategy

Feature tests cover end-to-end behavior for:

- feed filtering and fallback
- post creation and ownership-sensitive updates/deletes
- reaction endpoints and toggle behavior
- comment create/update/delete and nesting behavior
- follow/unfollow behavior including self-follow rejection

Unit tests cover:

- `ToggleReactionAction`
- `FeedService`
- `MediaUploadService`

## 14. Architectural Notes

- Domain models use explicit `$fillable` fields (no open mass assignment).
- Domain social entities use ULIDs for IDs.
- Controllers are intentionally thin; business logic lives in Actions/Services.
- Soft delete is used for posts/comments, with explicit cleanup where full cascade is needed.
- The repository tracks a development SQLite snapshot to simplify startup and review.