---
paths: resources/views/**/*.blade.php
---

# Blade Rules

## Components Over `@include`

```php
// ✅
<x-alert type="warning" :message="$message" />

// ❌
@include('partials.alert', ['type' => 'warning', 'message' => $message])
```

## Merge Attributes in Components

```php
// alert.blade.php
<div {{ $attributes->merge(['class' => 'alert']) }}>
    {{ $slot }}
</div>
```

## `@pushOnce` for Per-Component Assets

```php
@pushOnce('scripts')
    <script src="/js/datepicker.js"></script>
@endPushOnce
```

## View Composers for Shared Data

```php
View::composer('layouts.app', function (View $view) {
    $view->with('notifications', auth()->user()?->unreadNotifications);
});
```

## `@aware` for Nested Component Props

Access parent component data without re-passing:

```php
// In a child component
@aware(['color'])
<span class="text-{{ $color }}">{{ $slot }}</span>
```
