---
paths:
  - app/Console/**/*.php
  - routes/console.php
---

# Scheduling Rules

## Prevent Overlapping

For tasks with variable duration:

```php
$schedule->command('reports:generate')->daily()->withoutOverlapping();
```

## Single Server Execution

On multi-server deployments, run scheduled tasks on one server only:

```php
$schedule->command('reports:generate')->daily()->onOneServer();
```

## Run Long Tasks in Background

Allow concurrent execution of independent long tasks:

```php
$schedule->command('analytics:process')->daily()->runInBackground();
```

## Restrict by Environment

```php
$schedule->command('telescope:prune')->daily()->environments(['production']);
```

## Schedule Groups

Share configuration across related tasks:

```php
$schedule->group(attributes: [
    'onOneServer' => true,
    'withoutOverlapping' => true,
], schedule: function (Schedule $schedule) {
    $schedule->command('reports:daily')->daily();
    $schedule->command('reports:weekly')->weekly();
});
```
