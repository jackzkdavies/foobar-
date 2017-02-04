import numpy as np
from assign import mlp
import numpy.random 

# np.set_printoptions(threshold='nan')

numberReadIn = 13

# set up arrays for writing in pollens, 30samples each for training, 10samples each for testing and validation 
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

# loop over pollens, normilizing each column along the way and adding data to corresponding array
for c in xrange(numberReadIn):
    f = np.loadtxt('pollens/pollen{0}.dat'.format( c + 1 ))
    f = f/f.max(axis=0)
    
    for x in xrange(rowsReadTrain): 
        for i in xrange(43):
            tset[x+(c*rowsReadTrain)][i]=f[x][i]
    
    for x in xrange(rowsReadTestVal): 
        for i in xrange(43):
            vset[x+(c*rowsReadTestVal)][i]=f[x+30][i]
            teset[x+(c*rowsReadTestVal)][i]=f[x+40][i]
    
#     set up target arrays for each input array
    for i in range(nreadTrain/numberReadIn):
        train_tgt[i+(c*rowsReadTrain),c] = 1

        
    for i in range(nreadTestVal/numberReadIn):
        valid_tgt[i+(c*rowsReadTestVal),c] = 1
        test_tgt[i+(c*rowsReadTestVal),c] = 1

              
train_in = tset
test_in = teset
valid_in = vset

# shuffle traing data's and targets
def shuffle(a, b):
    state = numpy.random.get_state()
    numpy.random.shuffle(a)
    numpy.random.set_state(state)
    numpy.random.shuffle(b)
    
shuffle(train_in,train_tgt)


for i in [20,25]:   
    net = mlp.mlp(train_in,train_tgt,i,outtype='softmax')
    net.earlystopping(train_in,train_tgt,valid_in,valid_tgt,0.25,1200)
    net.confmat(test_in,test_tgt)



