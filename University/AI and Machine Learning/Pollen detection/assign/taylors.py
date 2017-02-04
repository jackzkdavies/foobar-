'''
Created on 15/09/2014

@author: Taylor
'''
import pylab as pl
import numpy as np

#create empty array to store target page values
targets = np.zeros((13*50,14), dtype=int)
currentRowNumber = 0

#read in the datasets
for x in range(1,14):
    if x == 1:
        data = np.genfromtxt('pollens/pollen'+str(x)+'.dat',dtype=float)
    else:    
        data = np.vstack([data,np.genfromtxt('pollens/pollen'+str(x)+'.dat',dtype=float)])
    #set the target array corresponding to the current page    
    currentRowNumber += 50
    targets[currentRowNumber - 50:currentRowNumber,x] = 1

#preprocessing    
data = data/data.max(axis=0)



#randomly order the arrays
change = range(np.shape(data)[0])
np.random.shuffle(change)
data = data[change,:]
targets = targets[change,:]

#set the training, test and validation inputs and targets
train = data[::2,:]
traint = targets[::2,:]

test = data[1::4,:]
testt = targets[1::4,:]


import som
markers = ['rv', 'gv', 'bv', 'ro','go','bo','rp','gp','bp','r*','g*','b*','r8','g8']
net = som.som(13,13,train)
net.somtrain(train,500)

best = np.zeros(np.shape(train)[0],dtype=int)
for i in range(np.shape(train)[0]):
    best[i],activation = net.somfwd(train[i,:])

pl.plot(net.map[0,:],net.map[1,:],'k.',ms=15)
for i in range(14):
    where = pl.find(traint[:,i] == 1)
    pl.plot(net.map[0,best[where]],net.map[1,best[where]],markers[i],ms=30)

pl.axis([-0.1,1.1,-0.1,1.1])
pl.axis('on')

#pl.figure(2)

#best = np.zeros(np.shape(test)[0],dtype=int)
#for i in range(np.shape(test)[0]):
#    best[i],activation = net.somfwd(test[i,:])
#
#pl.plot(net.map[0,:],net.map[1,:],'k.',ms=15)
#where = pl.find(testt == 0)
#pl.plot(net.map[0,best[where]],net.map[1,best[where]],'rs',ms=30)
#where = pl.find(testt == 1)
#pl.plot(net.map[0,best[where]],net.map[1,best[where]],'gv',ms=30)
#where = pl.find(testt == 2)
#pl.plot(net.map[0,best[where]],net.map[1,best[where]],'b^',ms=30)
#pl.axis([-0.1,1.1,-0.1,1.1])
#pl.axis('off')
pl.show()