A Simple and (I think) Secure FTP Browser
==========================================

NOTES
-----

* I reworked this from a working project. In doing so, I made it more generalized by adding settings.php. I'm pretty sure it will work, but I haven't tested it in its current state.

* In order for this to work, you are going to need a mysql db to hold the user accounts... so yeah... get that done before playing around

* This uses Bootstrap 3 for the front-end

* It should be able to handle large files, as it will download them in chunks

* My goal originally, and what I believe I have been successful in accomplishing, was to allow downloading from ftp without making it possible to have direct access to the ftp server. In practice, I used this with a Network Attached Storage device that had a shared folder set with ftp access behind a username and password. It is very handy for the office I made it for.
