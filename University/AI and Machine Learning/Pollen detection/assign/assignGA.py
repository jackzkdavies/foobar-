import ga
import numpy as np
import pylab as pl

popSize = 5
maxHiddenNodes= 32
# (43 plus one datapoints)
stringLength = 45*maxHiddenNodes

pop = np.random.rand(popSize, stringLength)
pop = np.where(pop<0.5,1,1)

pl.ion()
pl.show()

plotfig = pl.figure()

ga = ga.ga(stringLength,'fF.myGA',1001,popSize,0.1,'none',0,False)

ga.runGA(plotfig)

pl.pause(0)
pl.show()