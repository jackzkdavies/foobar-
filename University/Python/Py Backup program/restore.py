# Isaac Carrington
import init, os, os.path, sys, shutil, pickle

index = init.indexDir()
archive = init.objectsDir()
current = os.getcwd()

def restore(destDir):
    #     pickle load the dictionary
    check = open(index, "r")
    if os.path.getsize(index) == 0:
        print "Archive empty \n No files to restore"
        return
    else:
        initDict = pickle.load(check)
        check.close() 
    for keys in initDict:
        fileDir, fileName = os.path.split(keys)
        print keys
        print fileDir
        print fileName
        print ""
        filePath = os.path.join(str(destDir),fileDir) # This is the path that the file will need to be restored to
        if os.path.isdir(filePath): # If the path exists, copy the file there
            hashCode = initDict[keys] 
            copyFile(hashCode,fileName,fileDir,destDir)
        else:
            os.makedirs(filePath) # Make the required path
            hashCode = initDict[keys] 
            copyFile(hashCode,fileName,fileDir,destDir)  
    print "Archive backed up in %s" % (destDir)
        
       
'''
    Copy the file from the archive to the destination directory
    in the correct path.
'''      
def copyFile(hashCode,fullFileName,thePath,destDir):
    path = os.path.join(archive,hashCode)
    dir = os.path.join(str(destDir),thePath)
    shutil.copy2(path,dir)
    filePath = os.path.join(dir,hashCode)
    newName = os.path.join(dir,fullFileName)
    os.rename(filePath, newName)
