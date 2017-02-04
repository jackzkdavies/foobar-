'''
Created on 24/09/2014

@author: Zeb
'''
import numpy as np
import mlp
# np.set_printoptions(threshold='nan')
'''
Created on 24/09/2014

@author: Zeb
'''
import numpy as np
from assign import mlp
import numpy.random 
import math
import random

np.set_printoptions(threshold='nan')

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

for c in xrange(numberReadIn):
    f = np.loadtxt('pollens/pollen{0}.dat'.format( c + 1 ))
    #f = (f - f.mean(axis=0))
    f = f/f.max(axis=0)
    
    for x in xrange(rowsReadTrain): 
        for i in xrange(43):
            tset[x+(c*rowsReadTrain)][i]=f[x][i]
    
    for x in xrange(rowsReadTestVal): 
        for i in xrange(43):
            vset[x+(c*rowsReadTestVal)][i]=f[x+30][i]
            teset[x+(c*rowsReadTestVal)][i]=f[x+40][i]
    
    
    for i in range(nreadTrain/numberReadIn):
        train_tgt[i+(c*rowsReadTrain),c] = 1

        
    for i in range(nreadTestVal/numberReadIn):
        valid_tgt[i+(c*rowsReadTestVal),c] = 1
        test_tgt[i+(c*rowsReadTestVal),c] = 1

              
train_in = tset
test_in = teset
valid_in = vset

def shuffle_in_unison(a, b):
    rng_state = numpy.random.get_state()
    numpy.random.shuffle(a)
    numpy.random.set_state(rng_state)
    numpy.random.shuffle(b)
    
shuffle_in_unison(train_in,train_tgt)

def myGA(population):

    fitness = np.zeros((np.shape(population)[0],1))

    for i in range(np.shape(population)[0]):
        fitness[i] = getFitness(population[i])
 

    fitness = np.squeeze(fitness)
#    print "All fitness: ",fitness
    return fitness
    
    
def getFitness(string):
    
#    Gather the first 5 bytes to calculate the number of hidden nodes, set one at random to off
    hiddenNodesActivation=[0,0,0,0,0]
    weightAct = []
    for i in xrange(5):
            hiddenNodesActivation[i]=string[i]
            
    x = random.randint(0,4) 
    hiddenNodesActivation[x]=0
    array=np.array([16,8,4,2,1])
    hiddenNodesActivation=np.array(hiddenNodesActivation)
    hiddenNodes=np.sum(hiddenNodesActivation*array)
    hiddenNodes = int(hiddenNodes)+1
    
#     create the activation matric for the weights, depends in the number of hidden nodes, set one to off at random
    for i in range(44*hiddenNodes): 
            weightAct.append(string[i+5]) 
    
    x = random.randint(0,len(weightAct)-1)
    weightAct[x] = 0
    weightAct = np.array(weightAct).reshape((44,hiddenNodes))
   
#    create mlp and turn of weights ass set above
    net = mlp.mlp(train_in,train_tgt,hiddenNodes,outtype='softmax')
    weight1 = net.weights1
    net.weights1 = weight1*weightAct

#     Take a few steps through the MLP and then check the percent correct, return that as fitness
#     edit MLP to return % correct
    valid = np.concatenate((train_in,-np.ones((np.shape(train_in)[0],1))),axis=1)
    
    count = 0
    while (count <50):
        net.mlpfwd(valid)
        count +=1
 
    return net.confmat(test_in,test_tgt)
