---
paths: app/Jobs/**/*.php
---

# Job Rules

Jobs must implement `ShouldQueue` and use the `Queueable` trait:

```php
class ProcessVideoUpload implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Video $video,
    ) {}

    public function handle(VideoProcessor $processor): void
    {
        $processor->process($this->video);
    }
}

// Dispatch:
ProcessVideoUpload::dispatch($video);
```

For large datasets, chunk rather than loading all records:

```php
User::where('is_active', true)
    ->chunk(100, fn ($users) => $this->process($users));
```

## Retry & Backoff

Set `retry_after` to exceed the job `timeout`. Use exponential backoff and always implement `failed()`:

```php
class ProcessVideoUpload implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;
    public array $backoff = [1, 5, 10];

    public function handle(VideoProcessor $processor): void
    {
        $processor->process($this->video);
    }

    public function failed(Throwable $exception): void
    {
        // Notify, cleanup, etc.
    }
}
```

## Unique Jobs

Prevent duplicate jobs from being dispatched:

```php
class ProcessOrder implements ShouldQueue, ShouldBeUnique
{
    public function uniqueId(): string
    {
        return $this->order->id;
    }
}
```

## Rate Limiting

For jobs calling external APIs:

```php
public function middleware(): array
{
    return [new RateLimited('external-api')];
}
```

## Job Batching

For related jobs that should be tracked together:

```php
Bus::batch([
    new ProcessChunk($chunk1),
    new ProcessChunk($chunk2),
])->then(fn (Batch $batch) => /* all succeeded */)
  ->catch(fn (Batch $batch, Throwable $e) => /* first failure */)
  ->dispatch();
```
