---
paths: app/Http/Requests/**/*.php
---

# Form Request Rules

Every Form Request must define `authorize()` and `rules()`:

```php
class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
        ];
    }
}
```

## Only Use `validated()`

Never use `$request->all()` or `$request->input()` for mass operations:

```php
// ✅
$user = User::create($request->validated());

// ❌
$user = User::create($request->all());
```

## Conditional Validation

```php
'company' => [Rule::when($request->boolean('is_business'), ['required', 'string'])],
```

## Custom Validation Logic

Use `after()` over `withValidator()`:

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            if ($this->somethingElseFails()) {
                $validator->errors()->add('field', 'Message');
            }
        },
    ];
}
```
