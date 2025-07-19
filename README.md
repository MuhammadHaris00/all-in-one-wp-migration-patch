# Oaxpire - All-in-One WP Migration Patch

This plugin applies a patch to the **All-in-One WP Migration** plugin by modifying the required version of the **Unlimited Extension**.

It keeps a backup of the original required version, so you can safely restore it later.

---

## ✨ Features

- Update `'requires'` value in `class-ai1wm-extensions.php` for Unlimited Extension.
- Backup the original required version.
- Restore to the original required version at any time.
- Test script to check patching & restoring multiple times.

---

## 📦 Folder structure

```
your-plugin/
├── oaxpire-all-in-one-wp-migration-patch.php     # Main patch plugin
├── tests/
│   ├── test_v1.0.php                             # Test script
│   └── backup_version.oxp                         # Backup file (auto created)
└── README.md
```

---

## ⚙ Installation

1. Place this plugin inside your WordPress `wp-content/plugins` folder.
2. Make sure `All-in-One WP Migration` and `Unlimited Extension` are installed.
3. Activate the plugin from **WordPress admin**.

---

## 🧪 Running the test

The test script will:
- Patch to a new required version (e.g., `3.01`)
- Do this 3 times to test repetitiveness
- Then restore the old version 3 times
- Log what happened each time

### ✅ Steps

1. Open terminal in VSCode.
2. From your plugin folder, run:

```bash
php tests/test_v1.0.php
```

> ⚠ Note: Make sure WordPress context is loaded by editing the test file to include:
> ```php
> require_once __DIR__ . '/../../../wp-load.php';
> ```

3. Check terminal output:
   - You should see patch run logs & current `'requires'` value each time.
4. Also check `wp-content/debug.log` for additional logs.

---

## ✏️ How it works

Your main function signature is now:

```php
edit_ai1wm_plugin_file($old_version, $new_version);
```

- `$old_version` → the current version to look for (and optionally back up)
- `$new_version` → the new version you want to patch in

When patching:
- Pass the current version as `$old_version` and the desired new version as `$new_version`

When restoring:
- Read the backup file to get the original old version, and set `$new_version` to the old version to restore it

Example:

```php
$current_version = '2.68'; // current value
$new_version = '3.01';
edit_ai1wm_plugin_file($current_version, $new_version);
```

---

## 🔄 Restore the original version

When you run the restore part of the test:
- It reads the backup file (`backup_version.oxp`)
- Calls `edit_ai1wm_plugin_file($current_version, $old_version)` to revert

If you want to restore manually, you can also run:

```php
$old_version = trim(file_get_contents('backup_version.oxp'));
edit_ai1wm_plugin_file($current_version, $old_version);
```

---

## ⚠ Notes & best practices

- Always test on staging or local copy, **never directly on live**.
- Ensure correct file permissions: the plugin folder must be writable by the web server.
- The patch is **idempotent**: running it multiple times won't break anything.
- The backup file is created once and reused; it won't be overwritten on repeated patch runs.

---

## 🛠 For developers

- Adjust test script (`test_v1.0.php`) to use your desired versions.
- Use WP-CLI or an admin page if you prefer testing inside WordPress admin.
- Keep your WordPress debug log enabled during testing:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

---

## 📞 Need help?

If you need:
- A WP-CLI test command
- An admin page for patch testing
- Or a ready-to-deploy version

Just ask! 🚀

---

**Author:** Muhammad Haris - Oaxpire
