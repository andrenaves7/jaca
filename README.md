# Jaca 🍈

> It's not Java. It's Jaca.

Jaca is an experimental PHP microframework that lets you write web applications using a Java-inspired syntax — which are then **automatically transpiled to valid PHP code**.

The goal is to combine the familiarity of Java’s structure with the flexibility and productivity of PHP, while keeping features like:

- Modern PHP typing (`string`, `int`, `?array`)
- Default parameters
- Pass-by-reference
- PSR-4 autoloading
- MVC structure
- Extensible architecture

---

## ✨ Purpose

> Write in `.jaca`, run in `.php`.

Jaca consists of two main parts:

1. A high-level Java-like language (`.jaca`)
2. A transpiler implemented in PHP, embedded in the project, which converts `.jaca` files into modern PHP code automatically.

---

## 📁 Project Structure

```text
app/           ← Source code written in .jaca
src/           ← Generated PHP code
public/        ← Application entry point (index.php)
config/        ← Configuration files
storage/       ← Logs, cache, temporary files
tests/         ← Automated tests
vendor/        ← Composer dependencies
jaca           ← PHP CLI script to compile .jaca files (run with `php jaca compile`)
composer.json
