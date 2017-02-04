# Isaac Carrington
import init, os, os.path, sys, shutil, pickle
import initlogger

index = init.indexDir()
archive = init.objectsDir()
current = os.getcwd()


def get(fullFileName):
    fileFound = 0
    logging = initlogger.init_logging() # Get a logger
#     pickle load the dictionary
    check = open(index, "r")
    if os.path.getsize(index) == 0: # Check that the archive is not empty
        logging.warning("Retrieval of " + fullFileName + " failed because the archive is empty")
        return
    else:
        initDict = pickle.load(check)
        check.close() 
        
    pattern = str(fullFileName)
    '''
        Loop through the index and look for the file
    '''
    for keys in initDict:
        fileDir, fileName = os.path.split(keys)
        if pattern == None:
            print "Please enter a file name"
            break
        else:
            if pattern == fileName:
                fileFound = 1
                hashCode = initDict[keys]   
                if os.path.isfile(fullFileName):
                    print "A file with this name already exists \n Overwrite with %s? \n y/n" % (keys)
                    answer = raw_input().capitalize()
                    if answer == 'N':
                        print "File not overridden"
                    else:
                        print "Copying %s to the current directory" % (keys)
                        os.remove(fullFileName)
                        copyFile(hashCode,fullFileName)
                else:
                    print "Copying %s to the current directory" % (keys)
                    copyFile(hashCode,fullFileName)
    if fileFound == 0:
        print "No file was found"
                            
'''
    Copy the file from the archive to the current directory
'''            
def copyFile(hashCode,fullFileName):
    path = os.path.join(archive,hashCode)
    shutil.copy2(path,current)
    os.rename(hashCode, fullFileName)
    
                
               
                
                
                
            

