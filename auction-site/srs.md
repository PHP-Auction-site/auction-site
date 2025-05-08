

**Software Requirements Specification (SRS)**

**Project: Basic Auction Website**

**Version: 1.0**

**Date: May 8, 2025**

**1. Introduction**

*   **1.1 Purpose:** This document defines the requirements for a basic web-based auction application. It allows registered users to list items for auction and bid on items listed by others. The system will be built using HTML, CSS, and JavaScript for the frontend, PHP for the backend logic, and MySQL for data storage, based on the principles covered in the Internet Programming II course materials (Chapters 1-7).
*   **1.2 Scope:**
    *   **In Scope:** User registration and login, ability for registered users to list items (with title, description, category, starting price, end time, single image upload), ability for anyone to browse active auctions, filtering auctions by category, ability for registered users to place bids, display of highest bid and bidder, determination of winner based on auction end time, basic user dashboard (view listed items/items bid on). All times will be handled in GMT+3 (Ethiopian Time Zone).
    *   **Out of Scope:** Payment processing, real-time bidding updates (page refresh required), advanced search functionality (beyond category filtering), user-to-user messaging, user rating systems, administrative backend for site management, multi-lingual support, complex notification systems (email, etc.).
*   **1.3 Definitions, Acronyms, and Abbreviations:**
    *   **User:** An individual registered with the system. Can act as both Seller and Buyer.
    *   **Item:** An article listed for auction by a User.
    *   **Auction:** The process of selling an Item to the highest bidder within a set timeframe.
    *   **Bid:** An offer of a specific price for an Item made by a User.
    *   **Category:** A classification for Items (e.g., Electronics, Clothing, Collectibles).
    *   **Winner:** The User with the highest valid bid when the auction ends.
    *   **GMT+3:** Greenwich Mean Time + 3 hours (Ethiopian Time Zone). All date/time operations should use this timezone.
    *   **SRS:** Software Requirements Specification.
    *   **HTML:** HyperText Markup Language.
    *   **CSS:** Cascading Style Sheets.
    *   **JS:** JavaScript.
    *   **PHP:** PHP: Hypertext Preprocessor.
    *   **MySQL:** Relational Database Management System.
    *   **CRUD:** Create, Read, Update, Delete database operations.
    *   **API:** Application Programming Interface (used conceptually for internal structure).
*   **1.4 Overview:** This SRS document outlines the functional and non-functional requirements for the Basic Auction Website. Section 2 provides an overall description, including constraints and assumptions. Section 3 details the specific requirements, covering user functions, system behaviors, database structure, and security considerations.

**2. Overall Description**

*   **2.1 Product Perspective:** The Basic Auction Website is a self-contained, web-based application accessible via standard web browsers.
*   **2.2 Product Functions:**
    *   User account management (Registration, Login, Logout).
    *   Item listing creation and management (by the item's seller).
    *   Item browsing and filtering by category.
    *   Bidding on active auction items.
    *   Automatic determination of the winner based on the highest bid at the auction's end time.
    *   Basic user dashboard functionality.
*   **2.3 User Characteristics:** Users are expected to be familiar with general web browsing and online forms. No specific technical expertise beyond basic computer literacy is required. Any registered user can list items and place bids.
*   **2.4 Constraints:**
    *   The system must be developed using HTML, CSS, JavaScript (frontend), PHP (backend), and MySQL (database).
    *   All date and time operations must consistently use the GMT+3 timezone.
    *   Password storage must use secure hashing mechanisms provided by PHP (e.g., `password_hash()`).
    *   User input must be validated and sanitized to prevent common web vulnerabilities (XSS, basic SQL Injection via prepared statements).
    *   File uploads are limited to one image per item.
    *   Allowed image formats: JPEG, PNG, GIF.
    *   Maximum image file size: 2MB (configurable).
    *   Database interactions involving user input must use prepared statements (MySQLi or PDO).
    *   The system relies on page refreshes; real-time features are not required.
    *   Error handling should provide user-friendly messages and log detailed errors on the server where appropriate (as per production best practices discussed).
    *   Code should be reasonably modular using `include`/`require`.
*   **2.5 Assumptions and Dependencies:**
    *   The deployment server will have PHP and MySQL installed and configured correctly.
    *   Users will have JavaScript enabled in their browsers for any client-side enhancements/validation.
    *   The GD library (or similar) is available in PHP for potential image processing (like checking dimensions or type, though resizing isn't explicitly required for basic).

**3. Specific Requirements**

*   **3.1 Functional Requirements:**

    *   **FR-1: User Registration**
        *   FR-1.1: The system shall provide a registration form requiring a unique username, a valid email address, and a password.
        *   FR-1.2: The system shall validate input fields (required, email format).
        *   FR-1.3: Passwords shall be securely hashed before storing in the database.
        *   FR-1.4: The system shall prevent registration with an already existing username or email.
        *   FR-1.5: Upon successful registration, the user shall be notified and potentially logged in or redirected to the login page.
    *   **FR-2: User Login**
        *   FR-2.1: The system shall provide a login form requiring username and password.
        *   FR-2.2: The system shall verify the provided password against the stored hash.
        *   FR-2.3: Upon successful login, the system shall create a user session to maintain the logged-in state.
        *   FR-2.4: The system shall display an error message for invalid login attempts.
    *   **FR-3: User Logout**
        *   FR-3.1: Logged-in users shall have an option to log out.
        *   FR-3.2: Logging out shall destroy the user's session data.
    *   **FR-4: Item Listing**
        *   FR-4.1: Logged-in users shall be able to access a form to list a new item for auction.
        *   FR-4.2: The form shall require: Item Title, Description, Category (selected from a predefined list fetched from the database), Starting Price, Auction End Date and Time (using GMT+3).
        *   FR-4.3: The form shall allow uploading one image file (JPEG, PNG, GIF, max 2MB).
        *   FR-4.4: The system shall validate all submitted data (required fields, numeric price, valid date/time format, file type/size).
        *   FR-4.5: Uploaded images shall be stored securely on the server (e.g., in a designated uploads directory), and the path stored in the database. File handling should prevent directory traversal and ensure safe filenames.
        *   FR-4.6: Upon successful submission, the item shall be saved to the database with an 'Active' status, linked to the seller (logged-in user) and category.
    *   **FR-5: Browse Auctions**
        *   FR-5.1: All users (logged-in or not) shall be able to view a list of currently active auctions.
        *   FR-5.2: The list shall display key information for each item: Image thumbnail, Title, Current Price (highest bid or starting price), and Auction End Time.
        *   FR-5.3: Users shall be able to filter the auction list by Category. The category list should be dynamically populated from the database.
        *   FR-5.4: Users shall be able to click on an item to view its detailed page.
    *   **FR-6: View Item Details**
        *   FR-6.1: The item detail page shall display: Full Image, Title, Full Description, Category, Seller's Username, Starting Price, Current Highest Bid amount (if any), Current Highest Bidder's username (if any), Auction End Time.
        *   FR-6.2: If the auction has ended, the page shall clearly indicate this and display the Winner's username and winning bid amount (if applicable).
        *   FR-6.3: If the auction is active and the user is logged in, a bidding form shall be displayed.
    *   **FR-7: Place Bid**
        *   FR-7.1: Only logged-in users shall be able to place bids.
        *   FR-7.2: The bidding form shall require a bid amount.
        *   FR-7.3: The system shall validate the bid amount: It must be numeric and greater than the current highest bid (or starting price if no bids exist). (Define minimum increment, e.g., Current Bid + $1.00).
        *   FR-7.4: Users cannot bid on their own items.
        *   FR-7.5: The auction must be active (end time not passed).
        *   FR-7.6: Upon successful validation, the bid (amount, bidder ID, item ID, timestamp) shall be recorded in the database.
        *   FR-7.7: The item's current highest bid and highest bidder fields shall be updated in the `items` table (or retrieved via query).
        *   FR-7.8: The user shall receive on-screen confirmation of a successful bid or an error message if validation fails.
    *   **FR-8: Basic User Dashboard**
        *   FR-8.1: Logged-in users shall be able to access a dashboard section.
        *   FR-8.2: The dashboard shall display a list of items the user is currently selling (with status: Active/Ended).
        *   FR-8.3: The dashboard shall display a list of items the user has placed bids on, indicating if they are the current high bidder or if they won the auction.
    *   **FR-9: Timezone Handling**
        *   FR-9.1: PHP's default timezone shall be set to 'Africa/Addis_Ababa' (or equivalent GMT+3 identifier).
        *   FR-9.2: All displayed dates/times (e.g., auction end times, bid times) shall be formatted and presented relative to GMT+3.
        *   FR-9.3: Auction end time comparisons shall correctly use the GMT+3 timezone.

*   **3.2 Non-Functional Requirements:**

    *   **NFR-1: Security:**
        *   NFR-1.1: All user passwords must be hashed using `password_hash()`.
        *   NFR-1.2: All user input displayed on pages must be sanitized (e.g., using `htmlspecialchars()`) to prevent XSS.
        *   NFR-1.3: All database queries involving user input must use prepared statements (MySQLi or PDO) to prevent SQL injection.
        *   NFR-1.4: Session management should be secure.
        *   NFR-1.5: File uploads must be validated for type and size, and stored securely. Direct execution of uploaded files should be prevented.
    *   **NFR-2: Performance:**
        *   NFR-2.1: Web pages should load within a reasonable time frame (e.g., < 3-5 seconds) under normal conditions. Database queries should be optimized where necessary (e.g., using indexes on frequently queried columns like `item_id`, `user_id`, `category_id`).
    *   **NFR-3: Usability:**
        *   NFR-3.1: The user interface shall be clean, intuitive, and easy to navigate.
        *   NFR-3.2: Error messages shall be clear and informative.
    *   **NFR-4: Reliability:**
        *   NFR-4.1: The system should handle common errors gracefully (e.g., database connection failure, invalid input).
    *   **NFR-5: Maintainability:**
        *   NFR-5.1: PHP code should be well-commented and organized using includes/requires for separation of concerns (e.g., database connection, header, footer).

*   **3.3 Interface Requirements:**
    *   **UI-1:** The system shall be accessible via standard modern web browsers (e.g., Chrome, Firefox, Edge, Safari).
    *   **UI-2:** The user interface shall use HTML, CSS, and potentially minimal JavaScript for validation or minor dynamic effects.
*   **3.4 Database Requirements:**
    *   **DB-1:** The system shall use a MySQL relational database.
    *   **DB-1a:** The system shall support custom MySQL ports (e.g., 3307 for XAMPP) via a configurable environment variable in `.env` (DB_PORT).
    *   **DB-2:** The database schema shall include at least the following tables (columns are indicative):
        *   `users` (user\_id PK, username UNIQUE, email UNIQUE, password\_hash, registration\_date)
        *   `categories` (category\_id PK, category\_name)
        *   `items` (item\_id PK, user\_id FK, category\_id FK, title, description, starting\_price, current\_price, highest\_bidder\_id FK NULL, start\_time, end\_time, image\_path, status ENUM('Active', 'Ended'))
        *   `bids` (bid\_id PK, item\_id FK, user\_id FK, bid\_amount, bid\_time)
    *   **DB-3:** Appropriate indexes shall be created on foreign keys and frequently searched columns.
    *   **DB-4:** Relationships between tables (e.g., users-items, items-bids, items-categories) must be maintained using foreign keys.


**4. Project Organization**

*   **4.1 Folder Structure:**
    A well-organized folder structure is crucial for maintainability. The following structure is recommended:

    ```plaintext
    /auction_site/
    |
    |-- public/                 # Public root directory (document root for web server)
    |   |-- index.php           # Main entry point (router or controller)
    |   |-- css/                # CSS stylesheets
    |   |   `-- style.css
    |   |-- js/                 # JavaScript files (client-side validation/enhancements)
    |   |   `-- main.js
    |   |-- images/             # Static site images (logo, icons etc.)
    |   `-- uploads/            # Directory for user-uploaded item images (ensure correct permissions)
    |
    |-- src/                    # Source PHP files (outside public root if possible)
    |   |-- includes/           # Reusable PHP components
    |   |   |-- db_connect.php  # Database connection logic
    |   |   |-- functions.php   # Common utility functions (validation, sanitization, etc.)
    |   |   `-- session_check.php # Checks if user is logged in
    |   |
    |   |-- templates/          # HTML structure/templates (can be PHP files)
    |   |   |-- header.php
    |   |   |-- footer.php
    |   |   |-- item_card.php   # Template for displaying an auction item card
    |   |   `-- layout.php      # Optional main layout template
    |   |
    |   |-- actions/            # PHP scripts handling specific form submissions/actions
    |   |   |-- register_user.php
    |   |   |-- login_user.php
    |   |   |-- logout_user.php
    |   |   |-- create_item.php
    |   |   `-- place_bid.php
    |   |
    |   |-- pages/              # PHP scripts responsible for rendering specific pages
    |   |   |-- home.php        # Displays homepage/active auctions
    |   |   |-- item_details.php
    |   |   |-- login.php
    |   |   |-- register.php
    |   |   |-- create_listing.php # Page displaying the form to list item
    |   |   `-- dashboard.php
    |   |
    |   `-- config.php          # Configuration settings (DB credentials maybe loaded from .env here)
    |
    `-- .env                    # Environment variables (DB credentials, etc. - DO NOT COMMIT)
    ```

    *   **Note:** The exact structure can vary. For simpler projects, `actions/` and `pages/` might be combined or handled differently, perhaps via a simple router in `public/index.php`. Placing `src/` outside the public root enhances security.

*   **4.2 Conceptual API Routes / Backend Endpoints:**
    While not a strict REST API, these represent the backend PHP scripts that handle specific user actions and data processing. Many will render HTML pages as output.

    | Endpoint/Action             | HTTP Method | PHP Script/Handler        | Inputs                                     | Outputs/Action                                              | FR Ref     |
    | :-------------------------- | :---------- | :------------------------ | :----------------------------------------- | :---------------------------------------------------------- | :--------- |
    | **User Management**         |             |                           |                                            |                                                             |            |
    | View Registration Page    | GET         | `pages/register.php`      | -                                          | Renders registration form page                              | FR-1       |
    | Submit Registration       | POST        | `actions/register_user.php` | Username, email, password                  | Creates user, redirects to login or shows success/error     | FR-1       |
    | View Login Page           | GET         | `pages/login.php`         | -                                          | Renders login form page                                     | FR-2       |
    | Submit Login              | POST        | `actions/login_user.php`  | Username, password                         | Creates session, redirects to dashboard/home or shows error | FR-2       |
    | Logout User               | GET/POST    | `actions/logout_user.php` | Session ID (implicit)                      | Destroys session, redirects to homepage                   | FR-3       |
    | **Auction/Item Management** |             |                           |                                            |                                                             |            |
    | View Homepage/Auctions    | GET         | `pages/home.php`          | Optional `category_id` (query param)       | Renders list of active auctions, filtered by category     | FR-5       |
    | View Item Details         | GET         | `pages/item_details.php`  | `item_id` (query param)                    | Renders detailed page for a specific item                   | FR-6       |
    | View Create Listing Page  | GET         | `pages/create_listing.php`| Session ID (implicit)                      | Renders item listing form (requires login)                | FR-4       |
    | Submit New Item           | POST        | `actions/create_item.php` | Title, desc, category, price, end time, image | Creates item, handles image upload, redirects or shows msg | FR-4       |
    | Place Bid                 | POST        | `actions/place_bid.php`   | `item_id`, `bid_amount`, Session ID (implicit) | Records bid, updates item, redirects/refreshes item page | FR-7       |
    | **Dashboard**               |             |                           |                                            |                                                             |            |
    | View User Dashboard       | GET         | `pages/dashboard.php`     | Session ID (implicit)                      | Renders page showing user's items and bids (requires login) | FR-8       |

    *   **Note on Implementation:** In a basic PHP setup without a framework, `public/index.php` might act as a simple router, including the relevant file from `pages/` based on URL parameters (e.g., `index.php?page=item_details&id=123`). Form submissions would typically point directly to the corresponding script in `actions/`.
