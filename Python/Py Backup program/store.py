#Author Jack Davies 
import init, os, os.path, hashlib, time, sys, shutil, pickle, re, initlogger, datetime, time

archive = init.archiveDir()
index = init.indexDir()

def store(rootdir):
    
#   init logging   *******************************************************
    logging = initlogger.init_logging()
    ts = time.time()
    st = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')
    logging.info(st)
#   **********************************************************************
    
#   pickle load the dictionary of achieved files
    check = open(index, "r")
    print index
    if os.path.getsize(index) == 0:
        initDict = {}
    else:
        initDict = pickle.load(check)
        check.close() 

#   set the correct path
    os.chdir(rootdir)
    numberbackedUp = 0
    alreadyBackedUp = 0
    
    for folder, subs, files in os.walk(rootdir):
        for f in files:
            backedup = False

            path = os.path.join(folder,f)
            key = os.path.basename(rootdir) +  path.replace(rootdir, "")
            
            for keys in initDict:
                if keys == key:
                    alreadyBackedUp += 1
                    backedup = True


            if backedup == False:
                hash = createFileSignature(path)
                distination = str(archive) + str("/objects/") + hash 
                shutil.copy2(path,distination)
                initDict[key] = hash
                print "Backed Up: " + path
                logging.info(key)
                numberbackedUp += 1
                
                
            
            
    print "Backed Up: " + str(numberbackedUp)
    print "Already in Archive: " + str(alreadyBackedUp)
    
    fout = open(index, "w")
    pickle.dump(initDict, fout, protocol=0)
    fout.close()


       
#==========================================================================
def createFileSignature(filename):
#==========================================================================
    """CreateFileHash (file): create a signature for the specified file
       Returns a tuple containing three values:
          (the pathname of the file, its last modification time, SHA1 hash)
    """
    
    f = None
    signature = None
    try:
        filesize  = os.path.getsize(filename)
        modTime   = int(os.path.getmtime(filename))

        f = open(filename, "rb")  # open for reading in binary mode
        hash = hashlib.sha1()
        s = f.read(16384)
        while (s):
            hash.update(s)
            s = f.read(16384)

        hashValue = hash.hexdigest()   
#         signature = (filename,  modTime, hashValue)     
        signature = (hashValue) 
    except IOError:
        signature = None
    except OSError:
        signature = None
    finally:
        if f: 
            f.close()
    return(signature)

#=================================================================
# # Test signature creation
# sig =  createFileSignature('../slidyFiles/handout.css')
# print "SHA1 hash", sig

#=================================================================
# # Convert last modification time from numeric into printable form
# print "Last modified:", time.ctime(sig[1])
    
    
