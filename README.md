# 🛡️ Convert Forms Anti-Spam Script

A lightweight and fully self-contained **PHP Anti-Spam Script** for Joomla **Convert Forms**.

It prevents automated spam submissions based on text pattern analysis —  
no CAPTCHA, no external service, and fully GDPR-compliant.

---

## 🚀 Features

- 🔍 Detects **random character patterns** (e.g. `fzSyAESWqJNZLIjRerwVwM`)
- 🧩 Blocks **SQL injection attempts**
- ⚖️ Prevents **identical field values** (e.g. same first and last name)
- 🪵 Logs all blocked submissions to `/logs/convertforms_spam.log`
- 💡 Works **without CAPTCHA** or third-party APIs
- 🧠 100 % **GDPR-safe**
- 🔧 Debug mode for testing (simulation only, no form submission)

---

## ⚙️ Installation

You can integrate this script in **two different ways**, depending on your setup.

### Option 1 — Direct Insert *(recommended for single forms)*

Paste the **content** of `convertforms_antispam.php` directly into  
**Convert Forms → Behavior → PHP Script (Before Form Submission)**

> ⚠️ Do **not** include `<?php` or `?>` — just the code content.

This version runs immediately inside the Convert Forms process — no extra files required.

---

### Option 2 — External Include *(for multiple forms)*

1. Upload `convertforms_antispam.php` to:
   ```
   /libraries/convertforms_antispam.php
   ```
2. In Convert Forms → **Behavior → PHP Script**, insert:
   ```php
   require_once JPATH_ROOT . '/libraries/convertforms_antispam.php';
   ```

✅ Both methods work identically – only the integration differs.

---

## ⚙️ Configuration

At the top of the script, you can easily adjust the following parameters:

```php
// Path to the log file (recommended: outside your web root)
$logFilePath = realpath(JPATH_ROOT . '/../logs') . '/convertforms_spam.log';

// Enable or disable debug mode
// true  = simulate checks without sending the form
// false = live mode (form is sent normally)
$debugMode = false;

// Fields to compare (must not contain the same value)
$compareFields = [
    'nachname' => 'Name',
    'vorname'  => 'Vorname'
];

// Fields to check for random spam strings
$randomCheckFields = [
    'nachname' => 'Name',
    'vorname'  => 'Vorname',
    'firma'    => 'Firma'
];
```

> 💡 Tip: You can freely rename or remove fields.  
> If a configuration section (e.g. `$compareFields`) is left empty, that check will be skipped automatically.

---

## 🧩 Files

| File | Description |
|------|--------------|
| `convertforms_antispam.php` | German version |
| `convertforms_antispam_en.php` | English version |

Both versions are **identical in logic**, only comments and messages differ.

---

## 🪵 Log Example

```
[2025-11-05 21:38:02] Blocked (random string): Field=firstname | Value=bwUjwWluDLqUiBVSjFxBOmPY | IP=203.0.113.45
[2025-11-05 21:38:45] Blocked (same fields): firstname=Max | lastname=Max | IP=203.0.113.45
```

---

## 🧠 Notes

- When `$debugMode = true`, the form will **not** be submitted (simulation mode).
- When `$debugMode = false`, the script silently blocks spam and allows valid submissions.
- Log entries are stored outside the web root (default: `/logs/convertforms_spam.log`).

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.  
© 2025 [https://github.com/wk1337](https://github.com/wk1337) – **Kai W.**
