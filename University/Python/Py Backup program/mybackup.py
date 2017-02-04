import sys, get, init,  restore, store, test
import list

# print "sys.argv is: ", sys.argv
# 
# i = 0
# for arg in sys.argv:
#     print "sys.argv[%d] is %s" % (i, arg)
#     i += 1


if sys.argv[1] == "get":
        get.get(sys.argv[2])

elif sys.argv[1] == "init":
        init.init()

elif sys.argv[1] == "list":
        if len(sys.argv) > 2:
            list.list(sys.argv[2])
        else:
            list.list(None)

elif sys.argv[1] == "restore":
        restore.restore(sys.argv[2])

elif sys.argv[1] == "store":
            store.store(sys.argv[2])
        
elif sys.argv[1] == "test":
        test.test()

