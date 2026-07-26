---
name: wayfinder-development
description: "Use for Laravel Wayfinder work in this project, including connecting Vue or Inertia code to Laravel routes, wiring links, navigation, forms, Axios or fetch clients, changing frontend-facing routes, resolving generated route types, or running Wayfinder generation. This project generates named routes only under @/routes; use them instead of controller actions or hardcoded internal URLs. Do not use for backend-only changes that cannot affect frontend route calls."
---

# Wayfinder Development

## Project Contract

- Generate named routes only. `vite.config.ts` sets `actions: false`, and the Composer generation script passes `--skip-actions`.
- Import named exports from `@/routes`. Do not import from `@/actions`, because controller actions are not generated.
- Never edit `resources/js/routes` or `resources/js/wayfinder`; both are generated and ignored by Git.
- Name every route needed by frontend code. After adding or renaming one, regenerate definitions.
- Use generated route functions for internal Laravel URLs in Vue components, Inertia calls, Axios clients, and fetch calls.

## Documentation

Use `search-docs` before changing Wayfinder integration or relying on version-sensitive behavior.

## Generate Routes

The Vite plugin regenerates routes during development and builds. For a manual refresh, use the project script so route-only generation stays consistent:

```bash
composer wayfinder:generate
```

Do not use `--with-form` unless the project intentionally enables `formVariants`; conventional form variants are currently disabled.

If a deployment may have cached routes from a previous release, clear the route cache before the build generates definitions:

```bash
php artisan route:clear
npm run build
```

## Use Generated Routes

```typescript
import { checkout } from '@/routes/shop/ajax/cart'
import { show as showItem } from '@/routes/shop/item'

showItem(1) // { url: '/item/1', method: 'get' }
showItem.url(1) // '/item/1'
checkout.post() // { url: '/ajax/cart/checkout', method: 'post' }
```

Prefer named imports so unused route functions can be tree-shaken.

### Inertia

Pass the route object directly when Inertia accepts Wayfinder:

```vue
<script setup lang="ts">
import { Form, Link, router } from '@inertiajs/vue3'
import { login } from '@/routes'
import { show as showItem } from '@/routes/shop/item'

router.visit(showItem(1))
</script>

<template>
    <Link :href="showItem(1)">View item</Link>
    <Form :action="login()">
        <!-- fields -->
    </Form>
</template>
```

With `useForm`, call `form.submit(login())`. Avoid reducing a route object to `.url()` when Inertia can infer its HTTP method.

### Axios and fetch

Use `.url()` when the client API requires a string, and keep the HTTP method explicit:

```typescript
import ajax from '@/libraries/axios/common/ajax'
import { checkout } from '@/routes/shop/ajax/cart'

await ajax.post(checkout.url(), data)
```

### Parameters and query strings

Prefer the generated parameter shapes, especially for route model binding:

```typescript
import { index as itemIndex } from '@/routes/shop/ajax/item'
import { successfulPage } from '@/routes/shop/cart'
import { show as showItem } from '@/routes/shop/item'

showItem({ id: 1 })
successfulPage({ order: 1 })
itemIndex.url({ query: { page: 2 } })
itemIndex.url({ mergeQuery: { page: 2, filter: null } })
```

Use `query` to build a fresh query string and `mergeQuery` to preserve unrelated parameters from the current URL.

## Verification

1. Run `composer wayfinder:generate`.
2. Run `npm run type-check`.
3. Run focused backend tests for changed routes.
4. Verify affected navigation or requests with Playwright after frontend changes.

## Common Pitfalls

- Importing from `@/actions` even though actions are disabled.
- Hardcoding internal URLs in Vue or API client modules.
- Editing ignored generated files instead of their Laravel route definitions.
- Calling `.form()` when form variants are disabled.
- Passing only `.url()` to Inertia and losing the generated HTTP method.
- Forgetting to regenerate after route changes.
