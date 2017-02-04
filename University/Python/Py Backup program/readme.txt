159.251 Assignment 2
Software Engineering Design and Construction

Authors:
Isaac Carrington - Student ID 04329228
Jack Davies - Student ID 11252206


Table of Contents:
1 - Start command interface
2 - Navigate to program directory
3 - Create archive
4 - Backup folder
5 - Display list of backed-up files
6 - Get a file from the archive
7 - Restoring the archive
8 - Performing a test on the archive
9 - Logging feature



Program Instructions

1. Start your preferred command-line interface.In Windows 7 press the start button 
and type 'cmd' into the search box or alternatively right-click on the '254-2'
folder and select 'Open command-window here' (skip to instruction 3 is you done this).

2. Navigate to the 'Py Backup' directory by typing 'cd', a space, and then the path to 
the directory.

3. Create a archive directory by typing in the command 'python mybackup.py init'.
This will prompt you to create the directory. Type 'y' and press enter. If the archive
is already created you will be notified of this. The archive folder will be created on
your desktop.

It will contain an object folder that will contain backed-up files, and a index file which
will contain an index of backed-up files.

4. Add a folder to the archive by typing in the command 'python mybackup.py store "directory"',
where "directory" is the full path to the folder you wish to back up.

5. To display all the files backed-up in the archive type 'python mybackup.py list' into
the command-line. All files in the archive will now be displayed to you.

6. To get a file from the archive type the command 'python mybackup.py get "file name"'
into the command-line. Where "file name" is the full file name of the file you want, for
example 'main.c'.

The program will search the archive looking for files with this name. If no file is found
the program will advise you of this. If multiple files with this name are found the program
will ask whether you want to replace the currently saved file with the new one.

7. To restore all the contents of the archive into a directory type into the command-line
'python mybackup.py "destDir"'. Where "destDir" is the destination directory where you want
the archive to be restored to.

8. Checking the archive's contents can be done by using the 'test' function. Type
'python mybackup.py test' into the command line to run the test function.

This command runs two checks:

a) It checks that objects listed in the index actually exist in the objects directory.
b) Checks that the files in the objects have the correct content: that the hash of the file's 
contents and its name are the same.

The output is a count of correct entries, and the names of any erroneous paths or blobs.

9. 

















