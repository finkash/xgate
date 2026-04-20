# Architecture Skeleton

This project uses a domain-first skeleton so contributors can find business logic quickly.

## Folder map

- `app/Domain/IdentityAndAccess/`
  - `Actions/` registration/profile/follow actions
  - `DTOs/` request-to-domain data objects
  - `Models/` `Profile`, `Follow` domain models
- `app/Domain/Content/`
  - `Models/` `Post`, `PostMedia`
- `app/Domain/Engagement/`
  - `Actions/` `ToggleReactionAction`
  - `Enums/` `ReactionType`
  - `Models/` `Comment`, `Reaction`
- `app/Http/Controllers/`
  - transport layer only (validation + call action + return response)
- `app/Models/`
  - framework anchor model(s), currently `User`

## Layering rules

1. Controllers stay thin and call Actions.
2. Actions own business use-cases and may use transactions.
3. Models own relationships.
4. DTOs carry validated input into Actions.

## Current status

- Registration flow already uses `RegisterUserAction` + `RegisterUserDTO`.
- Reaction toggle already uses `ToggleReactionAction` + `ReactionType`.