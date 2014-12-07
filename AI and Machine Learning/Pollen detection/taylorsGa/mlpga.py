'''
Created on 21/09/2014

@author: Taylor
'''
import numpy as np

import mlp

import math

#create empty array to store target page values
targets = np.zeros((13*50,13), dtype=int)
currentRowNumber = 0

#read in the datasets
for x in range(1,14):
    if x == 1:
        data = np.genfromtxt('../assign/pollens/pollen'+str(x)+'.dat',dtype=float)
    else:    
        data = np.vstack([data,np.genfromtxt('../assign/pollens/pollen'+str(x)+'.dat',dtype=float)])
    #set the target array corresponding to the current page    
    currentRowNumber += 50
    targets[currentRowNumber - 50:currentRowNumber,x-1] = 1

#preprocessing    
data = data/data.max(axis=0)
#data = (data - data.mean(axis=0)/data.var(axis=0))


#randomly order the arrays
change = range(np.shape(data)[0])
np.random.shuffle(change)
data = data[change,:]
targets = targets[change,:]

#set the training, test and validation inputs and targets
train_in = data[::2,:]
train_tgt = targets[::2,:]

test_in = data[1::4,:]
test_tgt = targets[1::4,:]

valid_in = data[3::4,:]
valid_tgt = targets[3::4,:]

def mlpga(population):
    #print "this is current pop: ", population
    fitness = np.zeros((np.shape(population)[0],1))

    for i in range(np.shape(population)[0]):
#        print "At population number: ", i
        fitness[i] = getFitness(''.join(str(elem)[0] for elem in population[i]))
 

    fitness = np.squeeze(fitness)
#    print "All fitness: ",fitness
    return fitness

def getFitness(string):
    n = int(string.replace('.',''), 2)
#     print "Checking fitness for: ", string
#     print "Converted to decimal: ", n
    net = mlp.mlp(train_in,train_tgt,n,outtype='softmax')
    train = np.concatenate((train_in,-np.ones((np.shape(train_in)[0],1))),axis=1)
    fitness = net.mlpfwd(train)
    fitness = ((0.5*np.sum(fitness**2)))
    if (math.isnan(fitness) or fitness == 0):
        fitness = 1
#    fitness = net.confmat(test_in,test_tgt)

    #print "Fitness is: ", fitness
    return fitness