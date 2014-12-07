import numpy as np
from assign import mlp
import numpy.random 
import pylab as pl
import math

# np.set_printoptions(threshold='nan')
# set up arrays for writing in pollens, 30samples each for training, 10samples each for testing and validation 
numberReadIn = 13

rowsReadTrain = 30
nreadTrain = rowsReadTrain*numberReadIn
tset = np.zeros((nreadTrain,43))

rowsReadTestVal = 10
nreadTestVal = rowsReadTestVal*numberReadIn
vset = np.zeros((nreadTestVal,43))
teset = np.zeros((nreadTestVal,43))



train_tgt = np.zeros((nreadTrain,numberReadIn))
test_tgt = np.zeros((nreadTestVal,numberReadIn))
valid_tgt = np.zeros((nreadTestVal,numberReadIn))

#     read in pollens
pollens = np.zeros((650,43))
count = 0
for c in xrange(numberReadIn):
    pollen = np.loadtxt('pollens/pollen{0}.dat'.format( c + 1 ))

    for row in pollen:
        pollens[count]=row
        count +=1

# pollens = pollens - np.mean(pollens,axis=0)
# ymax = pollens.max(axis=0)
# ymin = pollens.min(axis=0)
# pollens = (pollens - ymin)/(ymax-ymin)


# more advanced normilaztion then MLP, numbers need to be in range 0 - 1
pollens = pollens - np.mean(pollens,axis=0)
imax = np.concatenate((pollens.max(axis=0)*np.ones((1,43)),pollens.min(axis=0)*np.ones((1,43))),axis=0).max(axis=0)
pollens[:,:] = pollens[:,:]/imax[:]

#     assign pollens to correct arrays, 30 from each for training, 10ea for testing and training
for pol in xrange(13):    
    for x in xrange(rowsReadTrain): 
        for i in xrange(43):
            tset[x+(pol*rowsReadTrain)][i]=pollens[(pol*50)+x][i]
 
    for x in xrange(rowsReadTestVal): 
        for i in xrange(43):
            vset[x+(pol*rowsReadTestVal)][i]=pollens[(pol*50)+30][i]
            teset[x+(pol*rowsReadTestVal)][i]=pollens[(pol*50)+40][i]
     
#        creat target matrix
    for i in range(nreadTrain/numberReadIn):
        train_tgt[i+(pol*rowsReadTrain),pol] = 1
     
         
    for i in range(nreadTestVal/numberReadIn):
        valid_tgt[i+(pol*rowsReadTestVal),pol] = 1
        test_tgt[i+(pol*rowsReadTestVal),pol] = 1
 
               
train_in = tset
test_in = teset
valid_in = vset

 
# print test_in
def shuffle_in_unison(a, b):
    state = numpy.random.get_state()
    numpy.random.shuffle(a)
    numpy.random.set_state(state)
    numpy.random.shuffle(b)
        
shuffle_in_unison(train_in,train_tgt)
   
actsTrain = np.zeros((390,169))
actsTest = np.zeros((130,169) )  
import som
markers = ['b1', 'g2', 'r3', 'c4','m1','y2','b3','g4','r1','c2','m3','y4','y1','m2']
net = som.som(13,13,train_in)
net.somtrain(train_in,400)
   
pl.figure(1)
count = 0
best = np.zeros(np.shape(train_in)[0],dtype=int)
for i in range(np.shape(train_in)[0]):
    best[i],activation = net.somfwd(train_in[i,:])
    actsTrain[count]=activation
    count+=1
    
for i in range(13):
    where = pl.find(train_tgt[:,i] == 1)
    pl.plot(net.map[0,best[where]],net.map[1,best[where]],markers[i],ms=15)
   
pl.axis([-0.1,1.1,-0.1,1.1])
pl.axis('on')

   
pl.figure(2)
count=0
best = np.zeros(np.shape(test_in)[0],dtype=int)   
for i in range(np.shape(test_in)[0]):
    best[i],activation = net.somfwd(test_in[i,:])
    actsTest[count]=activation
    count+=1

for i in range(13):
    where = pl.find(test_tgt[:,i] == 1)
    pl.plot(net.map[0,best[where]],net.map[1,best[where]],markers[i],ms=15)
   
pl.axis([-0.1,1.1,-0.1,1.1])
pl.axis('on')
   

actsTrain = actsTrain/actsTrain.max(axis=0) 

import pcn
p = pcn.pcn(actsTrain, train_tgt)
p.pcntrain(actsTrain, train_tgt,0.25,500)
p.confmat(actsTrain,train_tgt)

pl.show()