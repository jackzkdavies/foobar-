#Author Jack Davies 
import init, os, os.path, hashlib, time, sys, shutil, pickle

index = init.indexDir()


def list(input):
    count = 0
#     pickle load the dictionary
    check = open(index, "r")
    if os.path.getsize(index) == 0:
        initDict = {}
    else:
        initDict = pickle.load(check)
        check.close() 
        
    
    pattern = str(input)
        
    for keys in initDict:
        
        if input == None:
            count += 1
            print keys
        else:
            if pattern in str(keys):
                count += 1
                print keys
                
    if pattern == None:
        print "No pattern given, backup contains:", count, "items."
    else:
        print count, "items contain the pattern:", pattern, "*** NOTE: Patterns are case sensitive ***"
