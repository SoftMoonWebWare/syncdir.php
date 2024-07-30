# syncdir.php
synchronize, copy, &amp; compare filesystem folders / directories

# ¡ **Never** install on a publicly accessible server !
¡This program is designed to modify a (your) file system, copy files from ambiguous locations, overwrite existing files, etc.!
¡It has **NO** security implementations!
It is designed for a private server, only to utilize PHP's file-system access.

This **alpha** release has been tested in real-time use at various times in the past 2 years.
It it intended to:
1. keep a backup of "project folders" (and subfolders), only copying newly created or updated files.
2. synchronize project folders and subfolders when 2 (or more) folks are working on different files within.
3. compare 2 folders and subfolders for similar files and directory structures,
		as well as similar files in dis-similar directory structures.
4. create "playlists" on USB thumb-drives.

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

# 3. Compare folders
Either of the above two functions can be run **without** any files actually being copied;
syncdir.php will only report the files that need to be backed-up or synchronized.
You should always run syncdir in this mode first;
it will allow you to modify the sync-options and sync-filters until you get them the way you want them.

# 4. Create USB thumb "playlists"
Many car radios, etc., can play files on a USB thumb-drive; just plug it in.
But they don't let you modify the song order; the songs are played *in the order they were copied to the folder*.
Windows' drag & drop copy doesn't guarantee this order.
Using syncdir.php, make sure your song filenames start with a "track-number" (use 01, 02,...10, 11, etc. if more than 9 files),
then copy (backup) them to your thumb-drive with the "sort files" option checked.

# Options:
- check subfolders
- check file ages
- case-sensitive
- look for similar files
- preserve file creation time
- sort files alphabetically when copying
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

When using Windows, many times the OS tells PHP the filesize in KBs, MBs, etc., i.e. a “rounded estimate” instead of an exact number of bytes.
This can flubber the “find similar files” algorithm a bit, and it may show you completely unrealted files.
I want to be able to identify files that have had their names changed, even having been put into a different folder tree structure,
and filesize is the only way without opening each file and comparing the contents (¡too much overhead!).
Close is better than nothing or being way too slow.
¡Filesystems should put checksums in the directory and return exact filesizes!

When you are using Windows, files are already presented as sorted no matter what option you choose.
Windows passes the sorted file order to PHP, but Linux does not.
However, if you choose to override the "case insensitive" default for Windows and choose "case sensitive",
files may be re-ordered (uppercase comes before lowercase).

DISCLAIMER:
This ALPHA release has been tested on a Windows NT system with Apache 2.0 and PHP version 7.1.6 ; 8.1.6 ; 8.2.12
I’ve been using it for a while mostly without problems, except:
* I’ve seen my USB thumb-drive apparently overheat and become unresponsive.
 This is a hardware problem (video driver gets hot and heats up the metal-casing on the thumb-drive 1" away)
 and was solved by moving the thumb-drive to another USB port.
 However, PHP “locks-up” waiting for it, and I had to close the server and browser.
 It’s hard to know WHAT exactly PHP is doing…in that case I was transferring a large sum of data…
 was it going slow?  Five files copied (I could see in Windows Explorer), then nothing,
 and the browser just said “waiting on the server”.
* A few times the “verify first” option simply did not work.  WHY?  IDK!
 I didn’t have time to dig in and start logging everything PHP did, or even try again to do so.
 Other times it works perfectly.  When it fails, there is no error message,
 and the HTML interface seems normal.
 Just nothing gets synced/copied, as if you selected no files to sync/copy.
 I've looked at the code again and again, but did not see any reason why it might fail,
 other than the data did not transfer from the browser to PHP correctly for some reason.
 Playing with the filesystem while debugging is not something I want to do everyday,
 so IDK when I will look into that.
 I think I remember trying to sync many, many, many “verified first” files at once, and it failed.
 It worked when I only verified a few, if that’s a hint.
 Debugging code that fails under unknown circumstances is tricky.
 Doing so while your code is continuously modifying the filesystem is a real PITA!

It’s never actually skrewed up anything in the filesystem, but use at your own risk!
