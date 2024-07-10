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
3. compare 2 folders and subfolders for similar files and directory structures.
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
- filter by filename extension
- filter by file and folder names
- filter by file and folder paths
- filter by Regular Expression
- filter by POSIX Filesystem Wildcard Patterns

Find ***HELP*** popups throughout the interface.
