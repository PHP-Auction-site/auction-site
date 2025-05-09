# Basic Auction Website

This project is a web-based auction platform built with PHP, MySQL, HTML, CSS, and JavaScript. It allows users to register, list items for auction, place bids, and view auction results. The requirements and design are based on the Software Requirements Specification (see `srs.md`).

## Folder Structure

```
public/           # Web server document root
  index.php       # Main entry point
  js/             # JavaScript files
  images/         # Static images
  uploads/        # User-uploaded item images
src/              # PHP source files (outside public root)
  includes/       # Reusable PHP components
  templates/      # HTML/PHP templates
  actions/        # Form/action handlers
  pages/          # Page renderers
  config.php      # Configuration
.env              # Environment variables (not committed)
srs.md            # Software Requirements Specification
README.md         # This file
```

## Getting Started
- Set up a MySQL database as per the schema (to be provided).
- Configure database credentials in `.env` and `src/config.php`.
- **If using XAMPP with MySQL on port 3307, set `DB_PORT=3306` in your `.env` file.**
- Place the contents of `public/` in your web server's document root.
- Follow the SRS for feature details. 
