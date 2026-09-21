CCF Website Version 2 - Cameroon Cancer Foundation

Bilingual (EN/FR) website with working forms. Pages are plain HTML/CSS/JS; the
forms use a small PHP backend that stores submissions in a MySQL database.

FOLDER LAYOUT
  website\                 the finished site
    api\                   form backend (submit.php) + settings
    admin\                 password-protected page to read the submissions
    data\                  private folder (only used if you choose SQLite)
  configure-site.ps1       step 5 below: creates your private settings file
  build-for-hosting.ps1    step 6 below: makes the upload folder (dist\)
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

=====================================================================
EASIEST PATH (no FTP, no commands) - recommended for beginners
=====================================================================
 1. Network Solutions > Hosting > Manage > "Assign/Edit Your Domain": note which
    folder camcancerfoundation.org is assigned to (under /htdocs). This is your
    upload folder. NEVER upload into a folder that belongs to another domain.
 2. Hosting control panel > Database Manager (MySQL) > Add Database: create a NEW
    database (do not reuse an older one). Write down the database name, user and
    password.
 3. On your computer, double-click  1-configure-site.bat  and answer the questions
    (press Enter for the database host "localhost"; type the database details and
    choose an admin password).
 4. Double-click  2-build-upload-file.bat . It creates  ccf-website-upload.zip
    (about 23 MB) in this folder. Keep it private - it contains your database
    password.
 5. Network Solutions > Hosting control panel > File Manager: open the upload
    folder from step 1, click Upload, choose ccf-website-upload.zip (limit is
    100 MB per file). Then use "Archive Gateway" (Hosting control panel) to unzip
    it into that same folder. Delete the zip from the server afterwards.
 6. Continue with section D below to check it works.

=====================================================================
CONNECTING TO NETWORK SOLUTIONS - DETAILED / FTP ALTERNATIVE
=====================================================================
Network Solutions' Unix hosting supports PHP, MySQL and .htaccess. Everything below
is done by you, because it needs your account passwords.

A. IN YOUR NETWORK SOLUTIONS ACCOUNT (in a browser)
  1. Log in at networksolutions.com > "Websites & Hosting" > click "Manage" on the
     hosting package for this domain.
  2. PHP version: open "PHP Manager" (Configurations section) and pick PHP 7.4 or
     newer (the newest offered is fine).
  3. Database: open "Database Manager" (also called "MySQL Management") > MySQL >
     "Add Database". Choose a database name, a user and a password. Write down the
     database name, user, password and the host (normally "localhost"; use whatever
     the panel shows if it differs).
  4. FTP details: open "FTP Management" / "FTP Account Manager". Note the host,
     the username and the password (create/reset the password there if needed).
     These are different from your Network Solutions login.

B. ON YOUR COMPUTER (PowerShell, in this project folder)
  5. .\configure-site.ps1
       Asks for the database name/user/password (typed here, hidden), the email
       address that should receive alerts, and an admin password. It writes
       website\api\config.local.php on your computer only (never sent to GitHub).
  6. .\build-for-hosting.ps1 -Domain https://www.yourdomain.org
       Creates dist\ (and ccf-website-upload.zip): the exact files to upload.
       dist\ now contains your database password - keep it private, do not share
       or email it.

C. UPLOAD (FileZilla - the client Network Solutions recommends)
  7. New connection:
       Secure (preferred): Protocol SFTP, Host ftp.<your primary domain>,
                           Port 2222, Logon type Normal, your FTP username/password
       Or plain FTP:       Host ftp.<your domain>, Port 21
  8. On the server side, open the folder your domain serves from. For the primary
     domain on current hosting this is  public_html  (older packages use  htdocs).
     If unsure, it is the folder that already contains the default "coming soon"
     page.
  9. On your side, open the dist\ folder, select EVERYTHING inside it and drag it
     into that server folder. Do not upload the "dist" folder itself. Turn on
     Server > "Force showing hidden files" first so .htaccess is included
     (.htaccess must be sent as text/ASCII and end up with permission 644).
     If the site shows "500 Internal Server Error" right after, the .htaccess is
     the usual suspect: re-upload it in ASCII mode, or ask support.

D. CHECK IT WORKS
  10. Open https://yourdomain (home page, EN/FR toggle, audio, PDF downloads).
  11. Open https://yourdomain/admin/ and sign in with the admin password from step 5.
      Click "System check": everything should say OK. (Email alerts and HTTPS may say
      "Check" until you have an SSL certificate.)
  12. Send a test through EACH form in English and in French. Each one must appear
      in /admin/ and arrive by email. If email does not arrive, check spam; the
      submission is still safely stored in /admin/. Try a different mail_from
      (an address that exists on your domain) in config.local.php, or ask Network
      Solutions support about PHP mail settings.

E. LATER
  - SSL/HTTPS: when your certificate is active, run
      .\build-for-hosting.ps1 -Domain https://www.yourdomain.org -ForceHttps
    and re-upload .htaccess. Do NOT do this before https://yourdomain works.
  - After any edit: run step 6 again and upload only the changed files. Never
    delete or overwrite api\config.local.php on the server (or the data folder if
    you use SQLite).
  - If you forget the admin password: run .\configure-site.ps1 again, then upload
    the new api\config.local.php.

=====================================================================

USING THE ADMIN (/admin/)
  Filter by type/status, expand a row for the full message, Mark handled, Delete,
  and Export CSV (opens in Excel with accents intact). Sign in is throttled after
  5 wrong passwords. Only YOU can see submissions; visitors cannot.

REQUIREMENTS ON THE HOST
  PHP 7.3 or newer and MySQL (or the SQLite extension if you use -UseSqlite).
  Without PHP the pages still display, but the forms will show an error.

PRIVACY
  The forms store names, phone numbers, emails and (for screening) a region.
  Each form has a consent checkbox, but you should also publish a short privacy
  notice on the site describing how this information is used and kept. Do not ask
  visitors for medical details or payment card numbers through these forms.

LOCAL PREVIEW / TESTING
  Static look only: open website\index.html.
  With working forms (needs PHP, e.g. XAMPP's):  php -S localhost:8080 -t website
  Local test submissions go into website\data\ - the build script removes them from
  dist\ automatically.

NOTES
  - .htaccess (caching, compression, security headers, 404 page, and blocking of the
    private api/data files) works on Network Solutions' Unix/Apache hosting.
  - 404.html uses root-based paths (/assets/...). Edit its three references if you
    host in a sub-folder.
  - sitemap.xml and robots.txt default to https://www.camcancerfoundation.org (the
    address on contact.html); the -Domain option of the build script changes them.
  - Unused originals stay in website\assets\photos and website\downloads\projects
    and are uploaded too. Delete any you don't want online.
  - Forms are protected against spam by a hidden trap field and a limit of 8
    submissions per visitor per hour (rate_limit_per_hour in config).
  - Configuration sources: Network Solutions help articles on FTP/SFTP, MySQL
    Database Manager, PHP Manager and .htaccess support.

=====================================================================
LESSONS FROM THE REAL NETWORK SOLUTIONS DEPLOYMENT (camcancerfoundation.org)
=====================================================================
  - Account layout: one hosting package (main domain camoncenter.org). This site lives in
    the folder  /camcancerfoundation  and the domain is pointed to it under
    Hosting > WEBSITES & POINTERS > Pointers & Subdomains (type: Subdirectory).
    The older WordPress folder (wp_site_...) was left untouched.
  - The MySQL host is NOT localhost: use the "Server Name" shown on the database's Manage
    page (here mobitpaul40370.ipagemysql.com). Database names/users must be lowercase
    letters/numbers/underscores, 2-16 characters, and unique platform-wide.
  - Archive Gateway (Hosting tab > Hosting Tools > Launch) unzips uploaded files. Set both
    the file and the Output Directory to the site folder, never to the top-level root.
  - DELETE ccf-website-upload.zip from the server right after unzipping: it contains the
    database password. .htaccess also blocks *.zip and README.txt as a safety net.
  - .htaccess has no ErrorDocument line on purpose: /404.html resolves against the wrong root
    on this setup and produced a 500 error. A rewrite rule returns a plain 404 instead.
  - Network Solutions announced that PHP older than 8.4 will stop being supported. The site
    only needs PHP 7.3+, but re-test all forms after any PHP upgrade for this domain.
