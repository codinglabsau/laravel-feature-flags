# Upgrading

## v1 to v2

### Schema Changes

v2 adds two new nullable columns to the `features` table: `scope` and `description`.

If you published the migration when you first installed the package, create a new migration to add these columns:

```php
Schema::table('features', function (Blueprint $table) {
    $table->string('scope')->nullable()->after('state');
    $table->string('description')->nullable()->after('scope');
});
```

If you manage your schema outside of Laravel's migration system (e.g. multi-tenant setups, raw SQL), add the columns however your setup requires. The published migration stub reflects the full v2 schema and can be used as a reference.

### Config Format

v2 introduces a rich config format alongside the existing simple format. Both are supported — no changes are required to existing config files.

Simple format (unchanged):
```php
'features' => [
    'search-v2' => FeatureState::on(),
],
```

Rich format (new):
```php
'features' => [
    'search-v2' => [
        'state' => FeatureState::on(),
        'scope' => 'beta',
        'description' => 'New search powered by Meilisearch',
    ],
],
```

### Sync Behaviour

The sync action now updates `scope` and `description` on every deploy, matching whatever is in config. State is still only set when a feature is first created — this has not changed.

### Migrations Are No Longer Auto-Loaded

In v1, the package auto-loaded migrations from the vendor directory via `loadMigrationsFrom()`. In v2, migrations are publish-only. This aligns with Laravel's recommended approach for package migrations and gives you full control over your schema.

If you already published the migration in v1 (as the install instructions recommended), this change has no impact on you.

### Breaking Changes

- **Migrations are publish-only.** If you relied on migrations auto-loading from the vendor directory without publishing, you will now need to publish them: `php artisan vendor:publish --tag="feature-flags-migrations"`
- **Schema requires two new columns.** The `scope` and `description` columns must exist on the `features` table. Both are nullable, so existing data is unaffected.
- **SyncFeaturesAction writes `scope` and `description`.** If you have a custom feature model, ensure it allows mass assignment of these columns (or uses `$guarded = []`).
