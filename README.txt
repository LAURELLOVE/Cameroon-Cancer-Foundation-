CCF Website Version 2 - Cameroon Cancer Foundation

Bilingual (EN/FR) website with working forms. Pages are plain HTML/CSS/JS; the
forms use a small PHP backend that stores submissions in a database.

FOLDER LAYOUT
  website\                 <- the finished site. THIS is what you upload.
    api\                   form backend (submit.php) + settings (config.php)
    admin\                 password-protected page to read the submissions
    data\                  private folder for the database/secret (created on use)
  build-for-hosting.ps1    optional helper (see bottom)
  README.txt, .git ...     project files, NOT uploaded

WHAT VISITORS CAN SUBMIT (all saved to the database, emailed to you, bilingual)
  - Contact message .......... contact.html
  - Free screening request ... what-we-do.html#get-screened  (home "Get Screened" button)
  - Donation pledge .......... get-involved.html#donate      (header "Support a Project" button,
                               and "Support this project" on each project card)
  - Partnership inquiry ...... get-involved.html#partner
  - Volunteer application .... get-involved.html#volunteer
  The donation form only RECORDS a pledge - no payment is taken. You still need to
  send donors your verified bank / mobile-money details yourself.

REQUIREMENTS ON THE HOST
  PHP 7.3 or newer, plus MySQL (recommended) or the SQLite extension.
  Please confirm your Network Solutions hosting plan includes PHP and MySQL.
  Without PHP the pages still display, but the forms will show an error.

HOSTING ON NETWORK SOLUTIONS - COPY AND PASTE
  1. In your hosting control panel, create a MySQL database and a database user
     (note the host name, database name, user and password).
  2. Open website\api\, copy  config.local.sample.php  to  config.local.php  and
     fill in the database details, notify_email and mail_from. (Leave
     admin_password_hash empty for now.)
  3. Select EVERYTHING inside the "website" folder (index.html, api, admin, data,
     assets, downloads, .htaccess, ...) and upload it into your hosting account's
     web root so index.html sits at the top level (the folder name varies by plan:
     often public_html or htdocs - use whatever your FTP/File Manager shows as the
     site's main folder). Upload what is inside "website", not the folder itself.
       - FTP client (e.g. FileZilla): turn on "show hidden files" first, or
         .htaccess is skipped (FileZilla: Server > Force showing hidden files).
       - Windows Explorer copy includes hidden files automatically.
  4. Visit  https://yourdomain/admin/  - it asks you to choose an admin password
     and prints one line. Paste that line into api\config.local.php on the host,
     save, and sign in at /admin/.
  5. In the admin, open "System check". Everything should say OK.
     (If "Database file is private" says NO you are on SQLite and the host ignores
     .htaccess - switch to MySQL as in step 2.)
  6. Send yourself a test through each form, in English and French, and confirm it
     appears in /admin/ and arrives by email.

USING THE ADMIN (/admin/)
  Filter by type/status, expand a row for the full message, Mark handled, Delete,
  and Export CSV (opens in Excel with accents intact). Sign in is throttled after
  5 wrong passwords. Only YOU can see submissions; visitors cannot.

UPDATING THE SITE LATER
  Re-upload only the files you changed. Do NOT delete or overwrite the "data" folder
  or api\config.local.php on the host - they hold your live database (if SQLite)
  and settings.

DOMAIN + HTTPS
  - sitemap.xml and robots.txt say https://www.camcancerfoundation.org (the address
    on contact.html). Replace it if your domain is different.
  - Once an SSL certificate is active on your domain, open .htaccess and remove the
    leading "#" from the 5 lines under "Force HTTPS". Do not do this before
    https://yourdomain works, or visitors will get a certificate error.
  - Use https:// for /admin/ so your password is encrypted in transit.

PRIVACY
  The forms store names, phone numbers, emails and (for screening) a region.
  Each form has a consent checkbox, but you should also publish a short privacy
  notice on the site describing how this information is used and kept. Do not ask
  visitors for medical details or payment card numbers through these forms.

LOCAL PREVIEW / TESTING
  Static look only: open website\index.html.
  With working forms (needs PHP, e.g. XAMPP's):  php -S localhost:8080 -t website
  Local test submissions go into website\data\ - empty that folder (keep
  .htaccess and index.html) before uploading, or the build script does it for you.

OPTIONAL HELPER
  .\build-for-hosting.ps1 -Domain https://www.yourdomain.org [-ForceHttps]
  makes a copy in dist\ with the domain (and HTTPS redirect) already applied and
  local test data removed, plus ccf-website-upload.zip. The website folder is never
  modified.

NOTES
  - .htaccess (caching, compression, security headers, 404 page, and blocking of the
    private api/data files) works on Apache/Linux hosting. On a Windows/IIS plan it
    is ignored: use MySQL, and ask the host to block web access to api\config*.php
    and the data folder.
  - 404.html uses root-based paths (/assets/...). Edit its three references if you
    host in a sub-folder.
  - Unused originals stay in website\assets\photos and website\downloads\projects
    and are uploaded too. Delete any you don't want online.
  - Forms are protected against spam by a hidden trap field and a limit of 8
    submissions per visitor per hour (change rate_limit_per_hour in config).
