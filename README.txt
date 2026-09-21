CCF Website Version 2 - Cameroon Cancer Foundation

Static HTML/CSS/JS site (no database, no server-side code). Every path is
relative, so it works from the domain root or from a sub-folder.

LOCAL PREVIEW
  Open index.html in a browser (or run: npx serve .)

HOSTING ON NETWORK SOLUTIONS
  1. Build the upload package (PowerShell, in this folder):
       .\build-for-hosting.ps1
     Use your real domain and, once an SSL certificate is active on it, HTTPS:
       .\build-for-hosting.ps1 -Domain https://www.yourdomain.org -ForceHttps
     Do NOT use -ForceHttps until https://yourdomain works, or visitors will
     get a certificate error.
     This creates:  dist\  (folder)  and  ccf-website-upload.zip

  2. Upload the CONTENTS of dist\ (not the dist folder itself) into your
     hosting account's web root, so index.html sits at the top level.
     - FTP/SFTP client (e.g. FileZilla): connect with the FTP details from
       your Network Solutions account manager and drag everything across.
       Turn on "show hidden files" first, otherwise .htaccess is skipped.
       (FileZilla: Server > Force showing hidden files.)
     - Or, if your File Manager can extract zips, upload
       ccf-website-upload.zip to the web root and extract it there.

  3. Visit your domain and check: home page, EN/FR toggle, audio player,
     the PDF downloads on Projects and Resources, and a wrong address such
     as /nothing-here (should show the branded 404 page).

  4. After each future edit, run the build script again and re-upload.

NOTES
  - .htaccess (caching, compression, security headers, 404 page) works on
    Apache/Linux hosting. On a Windows/IIS plan it is ignored; the site
    still works, just without those extras.
  - 404.html uses root-based paths (/assets/...). If you ever host the site
    in a sub-folder, edit those three references.
  - The build defaults to https://www.camcancerfoundation.org (the address
    listed on contact.html) for sitemap.xml and robots.txt.
  - Unused originals stay in assets/photos and downloads/projects; they are
    uploaded too. Delete any you don't want online before building.
