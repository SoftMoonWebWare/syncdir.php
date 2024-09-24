# SyncDir.php
Synchronize, Copy, Archive, Create, &amp; Compare filesystem folders / directories

# ¡ **Never** install on a publicly accessible server !
¡This program is designed to modify a (your) file system, copy files from ambiguous locations, overwrite existing files, etc.!
¡It has **NO** security implementations!
It is designed for a private server, only to utilize PHP's file-system access.

This Beta release has been tested in real-time use at various times in the past 3 years.
It it intended to:
1. Keep a backup of "project folders" (and subfolders), only copying newly created or updated files within the same folder/directory tree structure.
2. Synchronize project folders and subfolders when 2 (or more) folks are working on different files within, keeping the same folder/directory tree structure.
3. Archive files from a source to a destination, with dis-similar folder/directory tree structures.
4. Compare 2 folders and subfolders for similar files and folder/directory tree structures,
		as well as similar files in dis-similar folder/directory tree structures.
5. Create "playlists" on USB thumb-drives for car radios, etc.

# 1. Backup folders
You can keep a backup of your folder in another location using this utility.
Modify the files in your main project folder; don't worry about remembering *which* files you modified.
Then run this syncdir utility, and it can automatically back up new files, or display them and ask you to verify which files to backup.

# 2. Synchronize folders
Similar to GitHub, but local, simple, and fully secure.
(the predecessor of this utility was written before GitHub, but it got lost somehow in a crash and a manual purge…)
Two or more folks work on their files within a project, updating the files on their local computer.
Then run this utility and sync the new files to a "central project folder" on USB "thumb-drive".
Files can be copied *to* the central project folder, as well as *from* the central project folder, in one step.
As with folder backup, you can verify each file first if you like.
If you have several archive folders with many of the same files in each but not all,
you can use syncdir.php to make one unified archive.
It can even find the files stuffed in different folders when your archive structures are dis-similar.

# 3. Archive files
Using the “Archive Mode” you can copy files from a source to a destination, avoiding any matching files found in an “archive folder”.
The “archive folder” may be any folder (the destination folder by default).
The folder/directory tree structure of the “archive folder” does not matter, and does not need to match the tree structure of the source.
However, when the files are copied, the folder/directory tree structure is maintained from the source to the destination.

# 4. Compare folders
Any of the above three functions can be run **without** any files actually being copied;
syncdir.php will only report the files that need to be backed-up or synchronized.
You should always run syncdir in this mode first;
it will allow you to modify the sync-options and sync-filters until you get them the way you want them.

# 5. Create USB thumb "playlists"
Many car radios, etc., can play media files on a USB thumb-drive; just plug it in.
But they don't let you modify the song order; the songs are played *in the order they were copied to the folder*.
Windows'® drag & drop bulk-copy doesn't guarantee this order; you must copy and then paste each file individually by hand to guarantee the order.
Using syncdir.php, you have a few options to control the song-order:
- make sure your song filenames start with a "track-number" (use 01, 02,...10, 11, etc. if more than 9 files),
then copy (backup) them to your thumb-drive with the "sort files" option checked.
- you can (further) sort your files by hand into the order you want using the mouse to drag and drop them.
SyncDir can automatically remove existing track numbers from the destination filenames if you don’t want them.
SyncDir can automatically add track numbers to the destination filenames in the order you arrange them, if you want.
When auto-adding track numbers, tracks are numbered in-continuum; i.e. the order they play.
If there are subfolders, the track numbers do *not* reset to “1” for each folder.
This way, you can find the track displayed on your car radio, no matter how many folders deep, with relative ease.
Otherwise, create each subfolder individually.

# Options:
- archive mode on/off
- check subfolders
- check file ages
- case-sensitive
- show file sizes
- look for similar files
- preserve file creation time
- sort files alphabetically when copying
- co-mingle (or not) folders with files when sorting
- remove existing track numbers
- add track numbers
- track number increment (when automatically adding track numbers)
- move old overwritten files to a trash folder
- various filtering options (filter in / filter out)
* control the order and method of filter application
* filter by filename extension
* filter by file and folder names
* filter by file and folder paths
* filter by Regular Expression
* filter by POSIX Filesystem Wildcard Patterns

Find ***HELP*** popups throughout the interface.

Basic **HELP**:

If you are keeping a backup of files that get updated regularly, and the backup gets synchronized TO the source folders as well as FROM the source folders,
I’ve found it best to create the original backup using syncdir.php, “keeping” the file modification times in the options.
Sometimes, using the OS interface to copy and paste files can update the “file creation time”, and along with that, the “file last modified time”;
then when you sync the folders, the backup wants to dump files back to the source that would not actually be newer.
Use syncdir.php from the start.

When syncdir.php shows you files to be / that have been synced, it will highlight files in red that overwrite files in the destination folder.

When "similar files" are found, click on the ▼ down-arrow to see them, the ▲ up-arrow to hide them.

When you are using Windows®, files are already presented as sorted no matter what option you choose.
Windows® passes the sorted file order to PHP, but Linux does not.
However, if you choose to override the "case insensitive" default for Windows® and choose "case sensitive",
files may be re-ordered (uppercase comes before lowercase).

DISCLAIMER:
This Beta release has been tested on a Windows® NT system with Apache 2.0 and PHP version 7.1.6 ; 8.1.6 ; 8.2.12
I’ve been using it for a while mostly without problems, except:
* I’ve seen my USB thumb-drive apparently overheat and become unresponsive.
 This is a hardware problem (video driver gets hot and heats up the metal-casing on the thumb-drive 1" away)
 and was solved by moving the thumb-drive to another USB port.
 However, PHP “locks-up” waiting for it, and I had to close the server and browser.
 It’s hard to know WHAT exactly PHP is doing…in that case I was transferring a large sum of data…
 was it going slow?  Five files copied (I could see in Windows® Explorer), then nothing,
 and the browser just said “waiting on the server”.
* Another time, something seemed to get whacked in the Windows® filesystem.
 I think my new USB drive doesn’t fit tight enough in the USB socket, or the socket may be wearing out.
 While using SyncDir to copy a large batch of files, it got hung up, similar to the above situation.
 I think the USB cord got knocked slightly and the drive was uninstalled and then reinstalled by Windows®
 while PHP was in the process of copying files.
 Again, this is a PHP thing and a hardware problem, not an internal logical problem with this software.
* A few times the “verify first” option simply did not work.  WHY?  IDK!
 I didn’t have time to dig in and start logging everything PHP did, or even try again to do so.
 Other times it works perfectly.  When it fails, there is no error message,
 and the HTML interface seems normal.
 Just nothing gets synced/copied, as if you selected no files to sync/copy.
 I've looked at the code again and again, but did not see any reason why it might fail,
 other than the data did not transfer from the browser to PHP correctly for some reason.
 Since then, I've been using it without any problem!  Weird, huh!
 The code has been updated, enhanced, expanded, etc, but the “verified” section remains fairly stable, so...?
 Playing with the filesystem while debugging is not something I want to do everyday,
 so IDK when I will look into that by trying to flubber it.
 I think I remember trying to sync many, many, many “verified first” files at once, and it failed.
 It worked when I only verified a few, if that’s a hint.
 However, again, since then, I’ve bulk copied a very large sum of files,
 again and again as I make a new SSD backup of all my stuff from a HDD archive,
 as well as sync two different old archive drives and my current working drive, with no problem.
 Debugging code that fails under unknown circumstances is tricky.
 Doing so while your code is continuously modifying the filesystem is a real PITA!

It’s never actually skrewed up anything in the filesystem, but use at your own risk!
