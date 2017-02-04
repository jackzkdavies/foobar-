# Isaac Carrington
import os, os.path, sys, pickle, init
import time
import logging
import logging.handlers
import store
import initlogger


index = init.indexDir()
archive = init.objectsDir()
count = 0 #  Used to count the number of correct entries in the index
'''
    Test the archive to ensure that objects listed in the index
    actually exist. Check that the hash of a files contents is
    the same as its name. Summary of the test is logged into
    myBackup.log, saved in the archive.
'''
def test():
    global count
    logger = initlogger.init_logging() # Get a logger
#     pickle load the dictionary
    check = open(index, "r")
    if os.path.getsize(index) == 0:
        logger.warning("The archive is empty")
    else:
        initDict = pickle.load(check)
        check.close() 
        # Loop through the index and check keys against the objects stored
        for keys in initDict:
            hashCode = initDict[keys]
            path = os.path.join(archive,hashCode)
            if os.path.isfile(path): # The file is in the objects directory
                hashCheck = store.createFileSignature(path)
                if hashCheck == hashCode: # The file has the correct hash code
                    logger.info(hashCode + " is correctly stored")
                    count = count + 1
                else:
                    logger.warning(hashCode + " exists but was hashed incorrectly")
            else:
                logger.warning(hashCode + " is listed in the index but does not actually exist")
        logger.warning("There were " + str(count) + " correct entries")
            

