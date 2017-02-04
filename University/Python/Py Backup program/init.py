#Author Jack Davies 
import os, os.path

myArchive = os.path.expanduser("~/Desktop/myArchive")
subDirectory = os.path.expanduser("~/Desktop/myArchive/objects")


def init():
    #Define Archive Location

    print "\n Directory will be created at", myArchive, "\n"
    
    if os.path.exists(myArchive) and os.path.isdir(myArchive):
        print "Archive Directory already exists"
      
    else:
        print "Archive Directory not yet created, \n would you like to create this directory at Desktop/myArchive?"
        print "Y, N"
      
        answer = raw_input().capitalize()
      
        if answer == "Y":
            print "Creating Archive Directory"
            os.makedirs(myArchive)
      
        else: 
            print "Archive Directory not created"
            return
    
      
    os.chdir(myArchive)
    if os.path.exists(subDirectory) and os.path.isdir(subDirectory):
        print "Sub Directory already exist"
        
    else:
        print "Creating Sub Directory"
        os.makedirs(subDirectory)
      
      
    index = './index.txt'
    if os.path.isfile(index):
        print "Index already exist"
          
    else:
        print "Creating Index"
        f = open("index.p",'w')
        f.close()

        
def archiveDir():
    return myArchive

def objectsDir():
    return subDirectory

def indexDir():
    return str(myArchive) +"/index.p"
      

