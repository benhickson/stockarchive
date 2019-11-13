# stockarchive

This app is tested to be used on:
- Ubuntu 16.04.6 LTS
- Apache/2.4.18 (Ubuntu)
- ffmpeg version 3.4.4-1~16.04.york0
- MySQL 5.7.27
- PHP Version 7.3.10-1+ubuntu16.04.1+deb.sury.org+1 (loaded as apache module)

Setup:
1. Install all above system requirements
2. Download blank database file (link) (or your database backup) and codebase (link to release)
3. Setup user and password for MySQL
4. Import blank database (or database backup) into MySQL
5. Expand codebase into a directory accessible by the public at yourdomain.dom/archive
6. Add database credentials to `includes/settings.php`
7. Setup folders to upload and transcode to, including setting permissions for your www-data (or other) web-server-user.
7. In MySQL, add a row to the users table to provision an account for a user, filling in the fields `email`, `firstname`, `lastname`, set `registastration_open`=1, and `registration_code`=any_two_character_code
8. Visit the URL in the browser, register an account (creating password) and log in.
