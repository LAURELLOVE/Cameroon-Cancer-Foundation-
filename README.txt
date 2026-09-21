CCF Website Version 2 - Cameroon Cancer Foundation

Static HTML/CSS/JS site (no database, no server-side code).

FOLDER LAYOUT
  website\                 <- the finished site. THIS is what you upload.
  build-for-hosting.ps1    optional helper (see bottom)
  README.txt, .git ...     project files, NOT uploaded

HOSTING ON NETWORK SOLUTIONS - COPY AND PASTE
  1. Open the "website" folder and select EVERYTHING inside it
     (index.html, assets, downloads, .htaccess, ...).
  2. Copy/upload it into your hosting account's web root so that
     index.html sits at the top level (the folder name varies by plan:
     often public_html or htdocs - use whichever your FTP/File Manager
     shows as the site's main folder). Do not upload the "website" folder
     itself, only what is inside it.
     - FTP client (e.g. FileZilla): connect with the FTP details from your
       Network Solutions account and drag everything across. Turn on
       "show hidden files" first, or .htaccess is skipped
       (FileZilla: Server > Force showing hidden files).
     - Windows Explorer copy also includes hidden files automatically.
  3. Visit your domain and check: home page, EN/FR toggle, audio player,
     the PDF downloads on Projects and Resources, and a wrong address such
     as /nothing-here (should show the branded 404 page).
  4. After any future edit, re-upload the changed files.

TWO THINGS TO ADJUST FOR YOUR DOMAIN (edit inside the website folder)
  - sitemap.xml and robots.txt currently say https://www.camcancerfoundation.org
    (the address on contact.html). Replace it if your domain is different.
  - HTTPS: once an SSL certificate is active on your domain, open .htaccess and
    remove the leading "#" from the 5 lines under "Force HTTPS". Do not do this
    before https://yourdomain works, or visitors will get a certificate error.

OPTIONAL HELPER
  .\build-for-hosting.ps1 -Domain https://www.yourdomain.org [-ForceHttps]
  makes a copy in dist\ with the domain (and HTTPS redirect) already applied,
  plus ccf-website-upload.zip. The website folder is never modified.

NOTES
  - Every path is relative, so the site also works from a sub-folder.
    Exception: 404.html uses root-based paths (/assets/...); edit its three
    references if you host in a sub-folder.
  - .htaccess (caching, compression, security headers, 404 page) works on
    Apache/Linux hosting. On a Windows/IIS plan it is ignored; the site still
    works without those extras.
  - Unused originals stay in website\assets\photos and website\downloads\projects
    and are uploaded too. Delete any you don't want online.
  - Local preview: open website\index.html, or run: npx serve website
